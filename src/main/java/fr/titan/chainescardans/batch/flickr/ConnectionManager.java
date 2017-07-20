package fr.titan.chainescardans.batch.flickr;

import com.flickr.api.Flickr;
import com.flickr.api.RequestContext;
import com.flickr.api.auth.Auth;
import com.flickr.api.auth.Permission;
import org.openqa.selenium.By;
import org.openqa.selenium.chrome.ChromeDriver;
import org.scribe.model.Token;
import org.scribe.model.Verifier;

import java.util.logging.Logger;

/**
 * User: Titan
 * Date: 20/06/13
 * Time: 17:46
 */
public class ConnectionManager {


    private static final String URL_CONNECT_FLICKR = "https://www.flickr.com/signin/";

    private static final ChromeDriver driver = new ChromeDriver();

    private static Logger logger = Logger.getLogger("ConnectionManager");

    /**
     * Return a verified token used to crypted all request
     * @param flickr
     * @param login
     * @param password
     * @return
     */
    public static Token signOnFlickr(Flickr flickr,String login,String password){
        Token t = flickr.getAuthInterface().getRequestToken();
        String url = flickr.getAuthInterface().getAuthorizationUrl(t, Permission.WRITE);
        logger.info("Try to connect to url " + url);
        driver.get(url);
        driver.findElementById("login-username").sendKeys(login);
        driver.findElementById("login-signin").click();
        try{
            Thread.sleep(4000);
        }catch(Exception e){}
        driver.findElementById("login-passwd").sendKeys(password);
        driver.findElementById("login-signin").click();


        int i = 0;
        while(!driver.getCurrentUrl().contains("flickr")){
            try{
                i++;
                Thread.sleep(200);
            }   catch(Exception e){}
        }
        driver.findElementById("permissions").findElement(By.tagName("form")).submit();
        String verif = driver.findElementById("Main").findElements(By.tagName("p")).get(1).findElement(By.tagName("span")).getText();
        driver.quit();

        // On recupere le token
        Token access = flickr.getAuthInterface().getAccessToken(t,new Verifier(verif));
        Auth a = new Auth();
        a.setPermission(Permission.WRITE);
        a.setToken(access.getToken());
        a.setTokenSecret(access.getSecret());
        RequestContext.getRequestContext().setAuth(a);

        return access;
    }
}

