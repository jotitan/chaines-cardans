package fr.titan.chainescardans.batch.flickr;

import com.flickr.api.Flickr;
import com.flickr.api.REST;
import com.flickr.api.uploader.UploadMetaData;
import org.apache.log4j.Logger;

import java.io.File;
import java.io.FileInputStream;
import java.io.FilenameFilter;
import java.util.Arrays;
import java.util.List;

/**
 * User: Titan
 * Date: 20/06/13
 * Time: 00:12
 */
public class UploadFlickr {
    protected static final String API_KEY = "195bad828fbaf0ef4fd82a7d32fadf94";

    private Logger logger = Logger.getLogger(UploadFlickr.class);
    private Flickr flickr;
    private ManageDirectory manageDirectory;

    public static void main(String[] args)throws Exception{
        if(args.length<2){return;}
        // Login et Mdp en 2 et 3
        UploadFlickr uploadFlickr = new UploadFlickr(args[2],args[3],args[4]);
        // Faire une boucle dans un repertoire
        String root = args[0];
        File[] directories = new File(root).listFiles(new FilenameFilter() {
            public boolean accept(File dir, String name) {
                return new File(dir.getAbsolutePath() + "/" + name).isDirectory()
                        && new File(dir.getAbsolutePath() + "/" + name + "/hd").isDirectory();
            }
        });
        for(File directory : directories){
            uploadFlickr.pushGallery(args[1],directory.getAbsolutePath() + "/hd",directory.getName());
        }
    }

    public UploadFlickr(String login,String pass, String secret)throws Exception{
        this.flickr = new com.flickr.api.Flickr(API_KEY,secret,new REST());
        ConnectionManager.signOnFlickr(this.flickr,login,pass);
        this.manageDirectory = new ManageDirectory(this.flickr);
    }

    public void pushGallery(String root,String path,String name){
        List<File> photos = Arrays.asList(new File(path).listFiles(new FilenameFilter() {
            public boolean accept(File dir, String name) {
                return name.toLowerCase().endsWith(".jpg");
            }
        }));
        uploadGallery(root,name,photos);
    }

    /**
     *
     * @param root : Nom de la collection
     * @param name : Nom du photoset
     * @param photos : Liste des photos
     */
    public void uploadGallery(String root, String name,List<File> photos){
        // Creation de la collection si elle n'existe pas
        String collectionId = manageDirectory.createCollectionsIfUtils(root);

        // On cree la gallerie s'il faut
        String photosetId = manageDirectory.createPhotosetIfUtils(collectionId,name);
        for(File photo : photos){
            try{
                logger.info("Upload photo " + photo.getName());
                String id = upload(photo);
                manageDirectory.addPhotoToPhotoset(id,photosetId);
                logger.info("Photo ajoutee a " + root + " (" + collectionId + ")");
            }catch(Exception e){
                logger.error(e.getMessage());
            }
        }
        manageDirectory.removePhotoFromPhotoset("9109129628",photosetId);
    }

    /**
     * Upload une image et renvoie l'id
     * @param file
     * @return Identifiant de l'image
     * @throws Exception
     */
    private String upload(File file)throws Exception{
        UploadMetaData data = new UploadMetaData();
        data.setTitle(file.getName());
        data.setPublicFlag(true);
        return this.flickr.getUploader().upload(new FileInputStream((file)), data);
    }
}
