package fr.titan.chainescardans.batch.flickr;

import com.flickr.api.Flickr;
import com.flickr.api.REST;
import junit.framework.TestCase;

/**
 * User: Titan
 * Date: 10/06/2017
 * Time: 14:31
 */
public class UploadFlickrTest extends TestCase{
    
    // Test connection flickr
    public void testConnection()throws Exception{
        System.setProperty("webdriver.chrome.driver", "src/main/resources/chromedriver.exe");
        Flickr f = new Flickr(UploadFlickr.API_KEY,"19fb6d00a9a317ae",new REST());
        ConnectionManager.signOnFlickr(f,"jotitan","!Flem1ng!");
        //ConnectionManager.connectToFlickr(f);
    }
}
