package fr.titan.chainescardans.batch.bean;

import org.codehaus.jackson.annotate.JsonIgnoreProperties;

import java.util.ArrayList;
import java.util.List;

/**
 * User: Titan
 * Date: 21/05/13
 * Time: 11:02
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class User {
    private String id;
    private String login;
    private List<DemandePhoto> demandes = new ArrayList();

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getLogin() {
        return login;
    }

    public void setLogin(String login) {
        this.login = login;
    }

    public List<DemandePhoto> getDemandes() {
        return demandes;
    }

    public void setDemandes(List<DemandePhoto> demandes) {
        this.demandes = demandes;
    }
}
