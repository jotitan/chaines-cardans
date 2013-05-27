package fr.titan.chainescardans.batch.ftp;

import java.io.File;
import java.io.InputStream;
import java.io.OutputStream;
import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;

import com.enterprisedt.net.ftp.*;
import org.apache.log4j.Logger;

/**
 * Fournit des methodes pour manipuler des donnees par ftp.
 * Cette couche rajoute un niveau transactionnelle (leger) qui permet de gerer les echecs de connexion
 * @author Titan
 *
 */
public class FtpManager {
    private Logger logger = Logger.getLogger("FtpManager");
    private final FileTransferClient ftp = new FileTransferClient();
    private final int nbEssai = 5;

    public FtpManager(String ftpPath,String login,String pass)throws Exception{
        ftp.setRemoteHost(ftpPath);
        ftp.setUserName(login);
        ftp.setPassword(pass);
        connect();
    }

    protected FileTransferClient getFtp(){
        if(ftp.isConnected() == false){
            connect();
        }
        return ftp;
    }

    private Object makeTraitement(String method,Class[] types,Object[] parametres,Class typeRetour){
        int nb = nbEssai;
        Method m = null;
        try{
            m = ftp.getClass().getMethod(method, types);
        }catch(NoSuchMethodException n){
            logger.error("La methode " + method + " n'existe pas");
            return typeRetour.cast(null);
        }
        // On tente n fois de lancer la methode ftp. A chaque erreur ftp (ITEx), on se deconncte / reconnecte
        while(nb-->0){
            try{
                if(m.getReturnType() == null){
                    m.invoke(getFtp(), parametres);
                    return true;
                }
                else{
                    Object o = m.invoke(getFtp(), parametres);
                    return o;
                }
            }catch(InvocationTargetException i){
                //on verifie la connection
                nb--;
                reconnect();
            }
            catch(Exception e){
                return typeRetour.cast(null);
            }
        }
        if(m.getReturnType() == null){
            return false;
        }
        else{
            return null;
        }
    }

    /**
     * Creer un repertoire
     * @param directory
     * @return
     */
    public boolean createDirectory(String directory){
        logger.info("Creation du repertoire " + directory);
        // verifie que le pere existe
        if(directory.lastIndexOf("/")!=-1 && !isDirectoryExist(directory.substring(0, directory.lastIndexOf("/")))){
            createDirectory(directory.substring(0, directory.lastIndexOf("/")));
        }
        Object o = makeTraitement("createDirectory", new Class[]{String.class},new Object[]{directory},Boolean.class);
        return (o==null)?false:(Boolean)o;
    }

    // Supprimer un repertoire
    public boolean deleteDirectory(String directory){
        Object o = makeTraitement("deleteDirectory", new Class[]{String.class},new Object[]{directory},Boolean.class);
        return (o==null)?false:(Boolean)o;
    }

    // Supprimer un fichier
    public boolean deleteFile(String file){
        Object o = makeTraitement("deleteFile", new Class[]{String.class},new Object[]{file},Boolean.class);
        return (o==null)?false:(Boolean)o;
    }

    // Envoyer un fichier
    public String uploadFile(String localFile,String remoteFile){
        logger.info("Upload du fichier " + remoteFile);
        return (String)makeTraitement("uploadFile", new Class[]{String.class,String.class},new Object[]{localFile,remoteFile},String.class);
    }

    // Envoyer un fichier, avec gestion du mode d'ecrasement
    public String uploadFile(String localFile,String remoteFile,boolean bruteMode){
        logger.info("Upload du fichier " + remoteFile);
        return (String)makeTraitement("uploadFile", new Class[]{String.class,String.class,WriteMode.class},new Object[]{localFile,remoteFile,(bruteMode)?WriteMode.OVERWRITE:WriteMode.RESUME},String.class);
    }

    public void uploadDirectory(File localDir, String remoteDir,boolean bruteMode){
        remoteDir = remoteDir.replace("//", "/");
        logger.info("Upload du repertoire " + localDir.getAbsolutePath() + " vers " + remoteDir);
        if(!isDirectoryExist(remoteDir)){
            createDirectory(remoteDir);
        }
        for(File file : localDir.listFiles()){
            if(file.isDirectory()){
                uploadDirectory(file, remoteDir + "/" + file.getName(), bruteMode);
            }else{
                if(!file.getName().endsWith(".db")){
                    uploadFile(file.getAbsolutePath(), remoteDir + "/" + file.getName(),bruteMode);
                }
            }
        }
    }

    // Telecharger un fichier en local
    public boolean downloadFile(String localFile,String remoteFile){
        return (Boolean)makeTraitement("downloadFile", new Class[]{String.class,String.class},new Object[]{localFile,remoteFile},Boolean.class);
    }

    // Charger un fichier
    public InputStream getFileInputStream(String file){
        return (InputStream)makeTraitement("downloadStream", new Class[]{String.class},new Object[]{file},Boolean.class);
    }

    // Recuperer un fichier en ecriture
    public OutputStream getFileOutputStream(String file){
        return (OutputStream)makeTraitement("uploadStream", new Class[]{String.class},new Object[]{file},Boolean.class);
    }

    // Indique si un repertoire ou un fichier existe
    public boolean isExist(String directory){
        return (Boolean)makeTraitement("exists", new Class[]{String.class},new Object[]{directory},Boolean.class);
    }

    /* Test si un repertoire existe */
    public boolean isDirectoryExist(String directory){
        String[] list = (String[])makeTraitement("directoryNameList",new Class[]{String.class,boolean.class},new Object[]{directory,false},String[].class);
        boolean existe = list!=null && list.length > 0;
        logger.info("Test repertoire " + directory + " : " + existe);
        return existe;
    }

    /**
     *
     * @param directory
     * @param count Correspond au nombre d'essai restant
     * @return
     */
    public boolean isDirectoryExist(String directory, int count){
        //try{
        String[] list = (String[])makeTraitement("directoryList",new Class[]{String.class},new Object[]{directory},String[].class);
        boolean existe = list!=null && list.length > 0;
        //boolean existe = getFtp().directoryList(directory).length > 0;
        logger.info("Test repertoire " + directory + " : " + existe);
        return existe;
        /*}catch(FTPException ftpe){
              if(ftpe.getMessage().indexOf("Exhausted active port retry count")!=-1 && count>0){
                  logger.error("Nouvel essai de test de repertoire " + directory + " (" + count + ")");
                  System.err.println(ftp.isConnected());
                  try{
                      ftp.disconnect();
                      ftp.connect();
                  }catch(Exception e) {
                      logger.error("Erreur : " + e.getMessage());
                  }
                  return isDirectoryExist(directory, count -1);
              }
              logger.error("Erreur test repertoire : " + directory + " => " + ftpe.getMessage());
              return false;
    }
    catch(Exception e){
        logger.error("Erreur isDirectory : " + e.getMessage());
        if(count>0){
            reconnect();
            return isDirectoryExist(directory,count--);
        }
        return false;
    } */
}

    /** Renvoie le nombre de fichiers presents dans le repertoire
     * @return : nombre de fichiers
     * */

    public long getNbFiles(String folder){
        String[] list = ((String[])makeTraitement("directoryNameList", new Class[]{String.class,boolean.class},new Object[]{folder,true},String[].class));
        int nb = (list!=null)?list.length:0;
        logger.info("Nb fichiers dans : " + folder + " : " + nb);
        return nb;
    }

    public void connect(){
        try{
            ftp.connect();
        }catch(Exception e){
            logger.error("Impossible de se connecter ",e);
        }
    }

    public void reconnect(){
        try{
            ftp.disconnect();
            ftp.connect();
        }catch(Exception e){}
    }

    public void disconnect(){
        try{
            ftp.disconnect();
        }catch(Exception e){}
    }


}
