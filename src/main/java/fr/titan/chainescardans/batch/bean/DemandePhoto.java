package fr.titan.chainescardans.batch.bean;

import org.codehaus.jackson.annotate.JsonIgnoreProperties;
import org.codehaus.jackson.annotate.JsonProperty;

/**
 * User: Titan
 * Date: 21/05/13
 * Time: 11:02
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class DemandePhoto {
    @JsonProperty("id")
    private Integer idDemande;
    private String path;

    public Integer getIdDemande() {
        return idDemande;
    }

    public void setIdDemande(Integer idDemande) {
        this.idDemande = idDemande;
    }

    public String getPath() {
        return path;
    }

    public void setPath(String path) {
        this.path = path;
    }
}
