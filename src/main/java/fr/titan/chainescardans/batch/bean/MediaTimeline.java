package fr.titan.chainescardans.batch.bean;

import org.codehaus.jackson.annotate.JsonIgnoreProperties;
import org.codehaus.jackson.annotate.JsonProperty;

/**
 * User: Titan
 * Date: 19/05/13
 * Time: 22:25
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class MediaTimeline {

    private String startDate;
    private String endDate;
    @JsonProperty("headline")
    private String title;
    @JsonProperty("data")
    private String idMedia;

    public String getStartDate() {
        return startDate;
    }

    public void setStartDate(String startDate) {
        this.startDate = startDate;
    }

    public String getEndDate() {
        return endDate;
    }

    public void setEndDate(String endDate) {
        this.endDate = endDate;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getIdMedia() {
        return idMedia;
    }

    public void setIdMedia(String idMedia) {
        this.idMedia = idMedia;
    }
}
