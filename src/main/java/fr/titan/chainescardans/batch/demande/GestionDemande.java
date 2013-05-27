package fr.titan.chainescardans.batch.demande;

import fr.titan.chainescardans.batch.Options;
import fr.titan.chainescardans.batch.bean.DemandePhoto;
import fr.titan.chainescardans.batch.bean.DemandeTraitement;
import fr.titan.chainescardans.batch.bean.User;
import fr.titan.chainescardans.batch.ftp.FtpUploader;
import org.apache.log4j.Logger;
import org.codehaus.jackson.map.ObjectMapper;
import org.kohsuke.args4j.CmdLineException;
import org.kohsuke.args4j.CmdLineParser;

import java.io.File;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;


public class GestionDemande {
    private Options options;
    private FtpUploader ftp;

    private final String drive = "Y:\\CHAINES_ET_CARDANS\\PHOTOS\\";
    private Logger logger = Logger.getLogger("GestionDemande");
    private static final String patternPentecote = ".*[0-9]{4}_[A-Z0-9]+/.+$";
    private static final String patternJournee = ".*[0-9]{4}_[0-9]{2}_[0-9]{2}_[A-Z0-9_]+/.+$";
    private static final String patternDivers = ".*[0-9]{4}_[0-9]{2}_[0-9]{2}_DIVERS_[A-Z0-9_]+/.+$";

    public static void main(String[] args)  {
        Options options = new Options();
        try{
            new CmdLineParser(options).parseArgument(args);
        }catch(CmdLineException ex){
            // Gestion des champs manquants
            return;
        }

        GestionDemande gd = new GestionDemande();
        gd.options = options;
        try{
            gd.runTraitement();
        }   catch(Exception e){}
    }

    public void runTraitement()throws Exception{
        List<User> users = RestRequestService.getDemandes();

        logger.info("Param : " + options.getFtpHost() + " , " + options.getUser() + " , " + options.getRemoteDirectory());
        this.ftp = new FtpUploader(options.getFtpHost(),options.getUser(),options.getPassword(),options.getRemoteDirectory());

        for (User user : users) {
            List<DemandeTraitement> demandes = this.treatDemandes(user);
            this.uploadListDemandes(demandes,user.getLogin());
            this.notifyUsers(user.getId(),demandes);
        }
        this.ftp.disconnect();
    }

    /* Recherche les photos et modifie les chemins */
    public List<DemandeTraitement> treatDemandes(User user) {
        List<DemandeTraitement> demandes = new ArrayList<DemandeTraitement>();
        for (DemandePhoto demande : user.getDemandes()) {
            DemandeTraitement dt = new DemandeTraitement();
            dt.setId(demande.getIdDemande());
            try {
                if (new File(drive + getDir(demande.getPath()) + demande.getPath()).exists()) {
                    dt.setPath(drive + getDir(demande.getPath()) + demande.getPath().replace("/ld/", "/hd/"));
                } else {
                    dt.setPath(null);
                }
                demandes.add(dt);
                dt.setStatus(DemandeTraitement.STATUS_OK);
            } catch (Exception e) {
                dt.setStatus(DemandeTraitement.STATUS_KO_NOT_FOUND);
            }
        }
        return demandes;

    }

    private String getDir(String path) throws Exception {
        String dir = "";
        if (path == null) {
            return dir;
        }
        if (path.matches(patternPentecote)) {
            return "PENTECOTE/";
        }
        if (path.matches(patternJournee)) {
            return "JOURNEE/";
        }
        if (path.matches(patternDivers)) {
            return "DIVERS/";
        }
        throw new Exception("");
    }

    /* Envoie les differentes photos par ftp */
    public void uploadListDemandes(List<DemandeTraitement> demandes,String login) {
        logger.info("Upload des demandes (" + demandes.size() + ")");
        for(DemandeTraitement d : demandes){
            if(d.getPath()!=null && d.getStatus() == DemandeTraitement.STATUS_OK){
                try{
                    //ftp.uploadPhoto(d.getPath(), login + "/uploaded/");
                    System.out.println("Upload de " + d.getPath() + " pour " + login);
                }catch(Exception e){
                    logger.info("Erreur upload " + d.getPath());
                    d.setStatus(DemandeTraitement.STATUS_KO_ERROR_UPLOAD);
                }
            }
        }
    }

    public void notifyUsers(String idUser,List<DemandeTraitement> demandes) {
        RestRequestService.updateStatusDemandes(idUser,demandes);
    }

}
