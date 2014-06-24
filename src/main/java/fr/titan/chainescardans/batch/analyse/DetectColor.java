package fr.titan.chainescardans.batch.analyse;

import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;
import java.awt.image.Raster;
import java.io.ByteArrayOutputStream;
import java.io.File;

/**
 *
 */
public class DetectColor {

    public String detectFirstColor(String path) throws Exception {
        File file = new File(path);
        BufferedImage img = ImageIO.read(file);
        Raster data = img.getData();

        int[] global = detectColor(data, false);
        int[] center = detectColor(data, true);

        return null;
    }

    private int[] detectColor(Raster data, boolean center) {
        double[][] results = new double[3][256];

        int beginX = center ? data.getWidth() / 4 : 0;
        int endX = (int) (center ? data.getWidth() * 0.75 : data.getWidth());
        int beginY = center ? data.getHeight() / 4 : 0;
        int endY = (int) (center ? data.getHeight() * 0.75 : data.getHeight());

        int totalPoints = (endX - beginX) * (endY - beginY);

        for (int x = beginX; x < endX; x++) {
            for (int y = beginY; y < endY; y++) {
                for (int color = 0; color < 3; color++) {
                    results[color][data.getSample(x, y, color)]++;
                    // results[color][data.getSample(x, y, color) / 5]++;
                }
            }
        }

        int[] moyennes = new int[3];
        int tempTotal[] = new int[3];
        int medianes[] = new int[3];
        int tempMax[] = new int[3];
        int max[] = new int[3];
        // Tester avec l'element le plus present. Regrouper tous les 5 pour lisser les resultats
        for (int x = 0; x < 256; x++) {
            for (int color = 0; color < 3; color++) {
                if (tempTotal[color] < totalPoints / 2) {
                    tempTotal[color] += results[color][x];
                } else {
                    if (medianes[color] == 0) {
                        medianes[color] = x;
                    }
                }
                moyennes[color] += (results[color][x] / totalPoints) * x;
                if (tempMax[color] < results[color][x]) {
                    tempMax[color] = (int) results[color][x];
                    max[color] = x;
                }
            }
        }

        // On arrondi
        for (int color = 0; color < 3; color++) {
            moyennes[color] = (moyennes[color] / 51) * 51 + ((moyennes[color] % 51 > 25) ? 51 : 0);
            medianes[color] = (medianes[color] / 51) * 51 + ((medianes[color] % 51 > 25) ? 51 : 0);
        }
        return medianes;
    }

    public String convertColorFromHexa(String color) {
        return null;
    }
}
