package fr.titan.chainescardans.batch;


import org.apache.log4j.Logger;
import org.kohsuke.args4j.CmdLineException;
import org.kohsuke.args4j.CmdLineParser;

import java.io.File;
import java.util.ResourceBundle;

/**
 * @author titan
 */
public class Main {

    public static String irfanDir = "c:\\Program Files\\IrfanView\\i_view32.exe";
    private static Logger logger = Logger.getLogger(Main.class);

    public static void main(String[] args) throws Exception{
        Options options = new Options();
        try{
            new CmdLineParser(options).parseArgument(args);
        }catch(CmdLineException ex){
            // Gestion des champs manquants
            logger.error(ex.getMessage());
            System.out.println(ex.getMessage());
            return;
        }
        if(true){
            return;
        }

        boolean tri = true;
        boolean bruteMode = false;

        ConvertisseurPhoto conv = new ConvertisseurPhoto();

        if(options.isRecursive()){
            conv.traiterRoot(options.getPhotosDirectory(), bruteMode, tri);
        }
        else{
            conv.traiterDir(new File(options.getPhotosDirectory()), tri, bruteMode);
        }

        /* Upload des photos converties*/
        new FtpUploader(options.getFtpHost(),options.getUser(),options.getPhotosDirectory(),options.getRemoteDirectory()).uploadPhotos(conv.getDirToUpdate(), bruteMode, options.getPhotosDirectory());
        System.out.println(conv.getUpdateScript(1));
    }
}
