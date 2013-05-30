package fr.titan.chainescardans.batch.demande;

import com.google.common.collect.ImmutableMap;
import com.sun.jersey.api.client.Client;
import com.sun.jersey.api.client.ClientResponse;
import com.sun.jersey.api.client.WebResource;
import com.sun.jersey.api.client.config.ClientConfig;
import com.sun.jersey.api.client.config.DefaultClientConfig;
import fr.titan.chainescardans.batch.bean.DemandeTraitement;
import fr.titan.chainescardans.batch.bean.TimelineResponse;
import fr.titan.chainescardans.batch.bean.User;
import org.codehaus.jackson.JsonNode;
import org.codehaus.jackson.map.ObjectMapper;
import org.codehaus.jackson.map.type.TypeFactory;
import org.codehaus.jackson.type.JavaType;
import org.omg.CORBA.PUBLIC_MEMBER;

import javax.ws.rs.core.Cookie;
import javax.ws.rs.core.NewCookie;
import java.io.IOException;
import java.math.BigInteger;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.List;
import java.util.Map;

/**
 * User: Titan
 * Date: 18/05/13
 * Time: 18:45
 */
public class RestRequestService {

    public static Cookie sessionCookie;    // Permet de garder l'authentification pour les requetes securisees

    public static void main(String[] args)throws IOException{
        String flux = "[{\"id\":188,\"status\":0},{\"id\":186,\"status\":1},{\"id\":187,\"status\":2},{\"id\":189,\"status\":0},{\"id\":190,\"status\":0}]";
        System.out.println(post("http://vbaranzini.free.fr/v3.0/MediaAction.php?action=9&idUser=1", flux));
    }

    /**
     * Liste les demandes des utilisateurs
     * @return
     * @throws IOException
     */
    public static List<User> getDemandes()throws IOException{
        String url = "http://vbaranzini.free.fr/v3.0/MediaAction.php";
        Map<String,String> params = ImmutableMap.of("action","7");
        return get(url, params, TypeFactory.defaultInstance().constructCollectionType(List.class, User.class), "users");
    }

    /**
     * Mets a jour le status des demandes sur le site.
     * @param idUser
     * @param demandes
     * @return
     */
    public static boolean updateStatusDemandes(String idUser,List<DemandeTraitement> demandes){
        try{
            ObjectMapper m = new ObjectMapper();
            String data = m.writeValueAsString(demandes);
            post("http://vbaranzini.free.fr/v3.0/MediaAction.php?action=9&idUser=" + idUser,data);
        }catch(IOException ioe){
            return false;
        }
        return true;
    }

    private static <T> T get(String url,Map<String,String> params,JavaType type)throws IOException{
        return get(url,params,type,null);
    }

    /**
     * Cree une requete en post.
     * @param url
     * @param data
     * @return
     */
    private static String post(String url,String data){
        ClientConfig config = new DefaultClientConfig();
        Client c = Client.create(config);
        WebResource resource = c.resource(url);
        WebResource.Builder builder = resource.getRequestBuilder();
        if(sessionCookie!=null){
            builder = resource.cookie(sessionCookie);
        }
        ClientResponse cr= builder.post(ClientResponse.class,data);
        return cr.getEntity(String.class);
    }

    private static <T> T get(String url,Map<String,String> params,JavaType type,String rootName)throws IOException{
        ClientConfig config = new DefaultClientConfig();
        Client c = Client.create(config);

        WebResource resource = c.resource(url);
        for(String key : params.keySet()){
            resource = resource.queryParam(key, params.get(key));
        }

        WebResource.Builder builder = resource.getRequestBuilder();
        if(sessionCookie!=null){
            builder = builder.cookie(sessionCookie);
        }
        ClientResponse response = builder.get(ClientResponse.class);
        String r = response.getEntity(String.class);
        ObjectMapper mapper = new ObjectMapper();
        if(rootName == null){
            return mapper.readValue(r,type);
        }
        else{
            JsonNode node = mapper.readTree(r).get(rootName);
            return mapper.readValue(node, type);
        }
    }

    /**
     * Permet de se connecter a la partie privee du site
     * @param login
     * @param pass
     * @return
     */
    public static boolean login(String login, String pass){
        try{
            ClientConfig config = new DefaultClientConfig();
            Client c = Client.create(config);

            String md5pass = new BigInteger(1,MessageDigest.getInstance("md5").digest(pass.getBytes())).toString(16);
            WebResource resource = c.resource("http://vbaranzini.free.fr/v3.0/SecuriteAction.php");
            resource = resource.queryParam("action","1").queryParam("login",login).queryParam("mdp",md5pass);
            ClientResponse cr = resource.get(ClientResponse.class);
            List<NewCookie> cookies = cr.getCookies();
            if(cookies == null || cookies.size() == 0){
                return false;
            }
            sessionCookie = cookies.get(0);
            return true;
        }catch(NoSuchAlgorithmException ex){
            return false;
        }
    }

}
