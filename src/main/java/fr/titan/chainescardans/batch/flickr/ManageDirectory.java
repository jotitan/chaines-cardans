package fr.titan.chainescardans.batch.flickr;

import com.flickr.api.Flickr;
import com.flickr.api.FlickrException;
import com.flickr.api.collections.Collection;
import com.flickr.api.photosets.Photoset;
import org.apache.log4j.Logger;

import java.util.List;

/**
 * Permet de gérer
 * User: Titan
 * Date: 20/06/13
 * Time: 00:25
 */
public class ManageDirectory {
    private Flickr flickr;
    private Logger logger = Logger.getLogger(ManageDirectory.class);
    public  ManageDirectory(Flickr flickr){
        this.flickr = flickr;
    }
    /**
     * Cree la collection si elle n'existe pas et renvoie son id
     * @param name
     * @return
     */
    public String createCollectionsIfUtils(String name){
        try{
            List<Collection> collections = flickr.getCollectionsInterface().getTree(null, null);
            for(Collection collection : collections ){
                if(name.equals(collection.getTitle())){
                    return collection.getId();
                }
            }
            // On le cree
            logger.info("Création du classeur " + name);
            Collection c = flickr.getCollectionsInterface().create(name);
            return c.getId();
        }catch(Exception e){
            return null;
        }
    }

    /**
     * Cree un photoset si c'est necessaire. Une fois cree, l'ajoute dans le classeur
     * @param collectionId
     * @param name
     * @return
     */
    public String createPhotosetIfUtils(String collectionId,String name){
        try{
            Collection c = flickr.getCollectionsInterface().getInfo(collectionId);
            for(Photoset photoset : c.getPhotosets()){
                if(name.equals(photoset.getTitle())){
                    return photoset.getId();
                }
            }
            logger.info("Creation du photoset " + name);
            Photoset p = flickr.getPhotosetsInterface().create(name,"Photos de " + name,"9109129628");
            flickr.getCollectionsInterface().addPhotoset(collectionId,p.getId());
            return p.getId();
        }catch(FlickrException fex){}
        return null;
    }

    public boolean addPhotoToPhotoset(String photoId,String photosetId){
        try{
            flickr.getPhotosetsInterface().addPhoto(photosetId,photoId);
            return true;
        }catch(Exception e){
            return false;
        }
    }

    public boolean removePhotoFromPhotoset(String photoId,String photosetId){
        try{
            this.flickr.getPhotosetsInterface().removePhoto(photosetId,photoId);
            return true;
        }   catch(Exception e){return false;}
    }

}
