package fr.titan.chainescardans.batch;


import fr.titan.chainescardans.batch.ftp.FtpUploader;
import org.apache.log4j.Logger;
import org.kohsuke.args4j.CmdLineException;
import org.kohsuke.args4j.CmdLineParser;

import java.io.File;
import java.util.List;

/**
 * @author titan
 */
public class Main {

    private static Logger logger = Logger.getLogger(Main.class);

    public static void main(String[] args) throws Exception{
        Options options = new Options();
        try{
            new CmdLineParser(options).parseArgument(args);
        }catch(CmdLineException ex){
            // Gestion des champs manquants
            logger.error(ex.getMessage());
            return;
        }

        FtpUploader ftpUploader = new FtpUploader(options.getFtpHost(),options.getUser(),options.getPassword(),options.getRemoteDirectory());

        /* Cas de la synchronisation */
        if(options.getSynchro()!=null){
            ConvertisseurPhoto conv = new ConvertisseurPhoto(0,0);
            conv.setFtpManager(ftpUploader.getFtpManager());
            List<String> list = conv.getSynchroDirectories(options.getPhotosDirectory(),options.getPhotosDirectory(),options.getRemoteDirectory(),options.getSynchro());
            System.out.println(list.size());
            ftpUploader.uploadPhotos(list, true, options.getPhotosDirectory());
        }
        else{

            boolean tri = true;
            boolean bruteMode = false;

            ConvertisseurPhoto conv = new ConvertisseurPhoto(options.getBigHeight(),options.getLowHeight());
            if(options.isRecursive()){
                conv.traiterRoot(options.getPhotosDirectory(), bruteMode, tri);
            }
            else{
                conv.traiterDir(new File(options.getPhotosDirectory()), tri, bruteMode);
            }
           /* Upload des photos converties*/
            ftpUploader.uploadPhotos(conv.getDirToUpdate(), bruteMode, options.getPhotosDirectory());
            System.out.println(conv.getUpdateScript(1));
        }
    }
}
