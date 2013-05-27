package fr.titan.chainescardans.batch.bean;

import org.codehaus.jackson.annotate.JsonIgnoreProperties;
import org.codehaus.jackson.annotate.JsonProperty;

import java.util.ArrayList;
import java.util.List;

/**
 * User: Titan
 * Date: 19/05/13
 * Time: 17:03
 * Represente une grise chronologique ou se trouve les differents medias
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class TimelineResponse {
    private String type;
    @JsonProperty("date")
    private List<MediaTimeline> medias = new ArrayList();

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }
}
