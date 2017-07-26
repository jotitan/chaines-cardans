package fr.titan.chainescardans.batch.analyse;

import junit.framework.Assert;
import junit.framework.TestCase;

import java.io.File;
import java.io.FileOutputStream;
import java.io.FilenameFilter;
import java.util.List;

/**
 * Created with IntelliJ IDEA. User: 960963 Date: 24/06/14 Time: 10:06 To change this template use File | Settings | File Templates.
 */
public class DetectColorTest extends TestCase {
    private DetectColor detectColor = new DetectColor();

    public void testDetectFirstColor() throws Exception {

        File file = File.createTempFile("test-result", ".html");
        FileOutputStream out = new FileOutputStream(file);
        for (String image : getTestImages()) {
            getColorResults("/" + image, out);
        }
        out.close();
        System.out.println(file.getAbsolutePath());
        // Assert.assertEquals("blue", color);

    }

    private String[] getTestImages() {
        return new File("target/test-classes").list(new FilenameFilter() {
            public boolean accept(File dir, String name) {
                return name.endsWith(".jpg");
            }
        });
    }

    private void getColorResults(String filename, FileOutputStream out) throws Exception {
        String path = getClass().getResource(filename).getPath();
        int[][][] infos = detectColor.detectFirstColor(path);

        out.write(("Result " + filename + "<br/>").getBytes());
        out.write(("<img src='" + path + "'/><br/>").getBytes());
        String block = "<span style='display:inline-block;width:50px;height:20px;background-color:";

        out.write("Global : ".getBytes());
        out.write(("AVG : " + block + formatColor(infos[0][0]) + "'></span>").getBytes());
        out.write(("MED : " + block + formatColor(infos[0][1]) + "'></span>").getBytes());
        out.write(("MAX : " + block + formatColor(infos[0][2]) + "'></span>").getBytes());
        out.write(("MAX GROUP : " + block + formatColor(infos[0][3]) + "'></span><br/>").getBytes());

        out.write("Centre : ".getBytes());
        out.write(("AVG : " + block + formatColor(infos[1][0]) + "'></span>").getBytes());
        out.write(("MED : " + block + formatColor(infos[1][1]) + "'></span>").getBytes());
        out.write(("MAX : " + block + formatColor(infos[1][2]) + "'></span>").getBytes());
        out.write(("MAX GROUP : " + block + formatColor(infos[1][3]) + "'></span><br/><hr/>").getBytes());
    }

    private String formatColor(int[] color) {
        return "rgb(" + color[0] + "," + color[1] + "," + color[2] + ")";
    }

    private String detectColor(int[] color) {
        if (color[0] > 200 && color[1] > 200 && color[2] < 100) {
            return "YELLOW";
        }
        if (color[0] > 200 && color[1] < 100 && color[2] < 100) {
            return "RED";
        }
        if (color[0] < 100 && color[1] < 100 && color[2] > 200) {
            return "BLUE";
        }
        if (color[0] < 100 && color[1] > 200 && color[2] < 100) {
            return "GREEN";
        }
        if (color[0] > 200 && color[1] < 100 && color[2] > 200) {
            return "PURPLE";
        }
        if (color[0] > 200 && color[1] > 200 && color[2] > 200) {
            return "WHITE";
        }

        if (color[0] < 100 && color[1] < 100 && color[2] < 100) {
            return "BLACK";
        }
        return "";
    }
}
