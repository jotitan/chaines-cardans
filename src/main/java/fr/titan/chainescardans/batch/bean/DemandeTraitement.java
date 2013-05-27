package fr.titan.chainescardans.batch.bean;

import org.codehaus.jackson.annotate.JsonIgnore;

/**
 * User: Titan
 * Date: 21/05/13
 * Time: 17:19
 * Represente le status de la demande apres le traitement
 */
public class DemandeTraitement {
    private Integer id;
    private Integer status = 0;
    @JsonIgnore
    private String path;    // Chemin physique de la photo

    public static final Integer STATUS_KO_ERROR_UPLOAD = 2;
    public static final Integer STATUS_KO_NOT_FOUND = 1;
    public static final Integer STATUS_OK = 0;

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public Integer getStatus() {
        return status;
    }

    public void setStatus(Integer status) {
        this.status = status;
    }

    public String getPath() {
        return path;
    }

    public void setPath(String path) {
        this.path = path;
    }
}
