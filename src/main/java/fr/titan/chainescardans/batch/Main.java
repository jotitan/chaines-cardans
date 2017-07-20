package fr.titan.chainescardans.batch;


import fr.titan.chainescardans.batch.flickr.UploadFlickr;
import fr.titan.chainescardans.batch.ftp.FtpUploader;
import org.apache.commons.lang3.StringUtils;
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
        System.setProperty("webdriver.chrome.driver", "src/main/resources/chromedriver.exe");

        Options options = new Options();
        UploadFlickr uploadFlickr = null;
        try{
            new CmdLineParser(options).parseArgument(args);
        }catch(CmdLineException ex){
            // Gestion des champs manquants
            logger.error(ex.getMessage());
            return;
        }

        FtpUploader ftpUploader = new FtpUploader(options.getFtpHost(),options.getUser(),options.getPassword(),options.getRemoteDirectory());

        /* Cas de l'upload sur Flickr en HD */
        if(StringUtils.isNotEmpty(options.getLoginFlickr())){
            uploadFlickr = new UploadFlickr(options.getLoginFlickr(),options.getPassFlickr(),options.getKeyFlickr());
        }

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
            boolean bruteWriteMode = true;

            ConvertisseurPhoto conv = new ConvertisseurPhoto(options.getBigHeight(),options.getLowHeight());
            if(options.isRecursive()){
                conv.traiterRoot(options.getPhotosDirectory(), bruteMode, tri);
            }
            else{
                conv.traiterDir(new File(options.getPhotosDirectory()), tri, bruteMode);
            }
           /* Upload des photos converties*/
            if(uploadFlickr!=null){
                for(String dir : conv.getHDFolders()) {
                    // Repertoire du type .../NAME/hd. Le nom a envoyer est NAME
                    String folderName = new File(dir).getParentFile().getName();
                    uploadFlickr.pushGallery(new File(options.getPhotosDirectory()).getName(),dir,folderName);
                }
            }
            ftpUploader.uploadPhotos(conv.getDirToUpdate(), bruteWriteMode, options.getPhotosDirectory());

            System.out.println(conv.getUpdateScript(1));
        }
        ftpUploader.getFtpManager().disconnect();
    }
}
