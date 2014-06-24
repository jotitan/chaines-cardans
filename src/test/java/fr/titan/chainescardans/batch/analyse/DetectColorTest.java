package fr.titan.chainescardans.batch.analyse;

import junit.framework.Assert;
import junit.framework.TestCase;

/**
 * Created with IntelliJ IDEA. User: 960963 Date: 24/06/14 Time: 10:06 To change this template use File | Settings | File Templates.
 */
public class DetectColorTest extends TestCase {

    public void testDetectFirstColor() throws Exception {
        DetectColor detectColor = new DetectColor();
        detectColor.detectFirstColor(getClass().getResource("/test-perso.jpg").getPath());
        detectColor.detectFirstColor(getClass().getResource("/test_bleu.jpg").getPath());
        detectColor.detectFirstColor(getClass().getResource("/test-moto.jpg").getPath());

        // Assert.assertEquals("blue", color);
    }
}
