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

import java.io.IOException;
import java.util.List;
import java.util.Map;

/**
 * User: Titan
 * Date: 18/05/13
 * Time: 18:45
 */
public class RestRequestService {

    public static void main(String[] args){
        String flux = "[{\"id\":188,\"status\":0},{\"id\":186,\"status\":0},{\"id\":187,\"status\":0},{\"id\":189,\"status\":0},{\"id\":190,\"status\":0}]";
        new RestRequestService().post("http://vbaranzini.free.fr/v3.0/MediaAction.php?action=9",flux);
    }

    public static List<User> getDemandes()throws IOException{
        String url = "http://vbaranzini.free.fr/v3.0/MediaAction.php";
        Map<String,String> params = ImmutableMap.of("action","7");
        return get(url, params, TypeFactory.defaultInstance().constructCollectionType(List.class, User.class), "users");
    }

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

    public void get() throws IOException {
        String url = "http://vbaranzini.free.fr/v3.0/MediaAction.php";
        Map<String,String> params = ImmutableMap.of("action", "1", "idGroup", "33");
        JavaType type = TypeFactory.defaultInstance().uncheckedSimpleType(TimelineResponse.class);
        TimelineResponse timeline = this.<TimelineResponse>get(url, params, type, "timeline");
    }

    public static <T> T get(String url,Map<String,String> params,JavaType type)throws IOException{
        return get(url,params,type,null);
    }

    public static String post(String url,String data){
        ClientConfig config = new DefaultClientConfig();
        Client c = Client.create(config);
        WebResource r = c.resource(url);
        ClientResponse cr= r.post(ClientResponse.class,data);
        return cr.getEntity(String.class);
    }

    public static <T> T get(String url,Map<String,String> params,JavaType type,String rootName)throws IOException{
        ClientConfig config = new DefaultClientConfig();
        Client c = Client.create(config);
        WebResource resource = c.resource(url);
        for(String key : params.keySet()){
            resource = resource.queryParam(key,params.get(key));
        }
        ClientResponse response = resource.get(ClientResponse.class);
        String r = response.getEntity(String.class);
        // Mapping
        ObjectMapper mapper = new ObjectMapper();
        if(rootName == null){
            return mapper.readValue(r,type);
        }
        else{
            JsonNode node = mapper.readTree(r).get(rootName);
            return mapper.readValue(node, type);
        }
    }

}
