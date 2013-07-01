package fr.titan.chainescardans.batch.flickr;

import com.flickr.api.Flickr;
import com.flickr.api.RequestContext;
import com.flickr.api.auth.Auth;
import com.flickr.api.auth.Permission;
import org.openqa.selenium.By;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.scribe.model.Token;
import org.scribe.model.Verifier;

import java.util.Iterator;

/**
 * User: Titan
 * Date: 20/06/13
 * Time: 17:46
 */
public class ConnectionManager {


    private static final String URL_CONNECT_FLICKR = "http://www.flickr.com/signin/";
    private static final FirefoxDriver driver =  new FirefoxDriver();


    public static Token connectToFlickr(Flickr f)throws Exception{
        Token t = f.getAuthInterface().getRequestToken();
        String url = f.getAuthInterface().getAuthorizationUrl(t, Permission.WRITE);
        driver.get(url);
        driver.findElementById("permissions").findElement(By.tagName("form")).submit();
        // On recupere le code de verification
        String verif = driver.findElementById("Main").findElements(By.tagName("p")).get(1).findElement(By.tagName("span")).getText();
        driver.quit();

        // On recupere le token
        Token access = f.getAuthInterface().getAccessToken(t,new Verifier(verif));
        Auth a = new Auth();
        a.setPermission(Permission.WRITE);
        a.setToken(access.getToken());
        a.setTokenSecret(access.getSecret());
        RequestContext.getRequestContext().setAuth(a);

        return access;
    }
    public static boolean signOnFlickr(String login,String password){
        driver.get(URL_CONNECT_FLICKR);
        driver.findElementById("gBtnLnk").click();
        Iterator it = driver.getWindowHandles().iterator();
        it.next();
        driver.switchTo().window(it.next().toString());
        System.out.println(driver.getCurrentUrl());
        driver.executeScript("document.getElementById('Email').value = '" + login + "'");
        driver.executeScript("document.getElementById('Passwd').value = '" + password + "'");

        driver.findElementById("signIn").click();
        int i = 0;
        while(!driver.getCurrentUrl().contains("flickr")){
            try{
                i++;
                Thread.sleep(200);
            }   catch(Exception e){}
        }
        System.out.println(i);
        return true;
    }
}

