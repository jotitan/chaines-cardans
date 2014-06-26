package fr.titan.chainescardans.batch.analyse;

import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;
import java.awt.image.Raster;
import java.io.File;
import java.util.HashMap;

/**
 *
 */
public class DetectColor {

    public int[][][] detectFirstColor(String path) throws Exception {
        File file = new File(path);
        BufferedImage img = ImageIO.read(file);
        Raster data = img.getData();

        int[][] ciel = detectColor(data, 3);
        int[][] global = detectColor(data, 0);
        int[][] center = detectColor(data, 2);
        if (new KeyColor(ciel[3]).isBlue()) {
            System.out.println(path + " => blue");
            global = detectColor(data, 6);
        }
        return new int[][][] { global, center };
    }

    class KeyColor {
        private int red;
        private int green;
        private int blue;

        public KeyColor(int red, int green, int blue) {
            this.red = red;
            this.green = green;
            this.blue = blue;
        }

        public KeyColor(int[] colors) {
            this.red = colors[0];
            this.green = colors[1];
            this.blue = colors[2];
        }

        public KeyColor(int red, int green, int blue, int round) {
            this.red = roundTo(red, round);
            this.green = roundTo(green, round);
            this.blue = roundTo(blue, round);
        }

        public int[] toColor() {
            return new int[] { red, green, blue };
        }

        public boolean isGray() {
            return red == green && green == blue;
        }

        public boolean isBlue() {
            return (blue >= red * 2 && blue >= green * 2) || (blue == 255 && green < 255 && red < 255);
        }

        @Override
        public boolean equals(Object o) {
            if (this == o)
                return true;
            if (o == null || getClass() != o.getClass())
                return false;

            KeyColor keyColor = (KeyColor) o;

            if (blue != keyColor.blue)
                return false;
            if (green != keyColor.green)
                return false;
            if (red != keyColor.red)
                return false;

            return true;
        }

        @Override
        public int hashCode() {
            int result = red;
            result = 31 * result + green;
            result = 31 * result + blue;
            return result;
        }
    }

    /**
     * 
     * @param type
     *            : 0- tout, 1- centre (1/4), 2- centre (1/3), 3- tiers superieur, 4- tiers central, 5- tiers inferieur, 6- 2 tiers inferieur
     * @return
     */
    private int[] getBoundsImage(int type, Raster data) {
        switch (type) {
        case 0:
            return new int[] { 0, data.getWidth(), 0, data.getHeight() };
        case 1:
            return new int[] { data.getWidth() / 4, data.getWidth() * 3 / 4, data.getHeight() / 4, data.getHeight() * 3 / 4 };
        case 2:
            return new int[] { data.getWidth() / 3, data.getWidth() * 2 / 3, data.getHeight() / 3, data.getHeight() * 2 / 3 };
        case 3:
            return new int[] { 0, data.getWidth(), 0, data.getHeight() / 3 };
        case 6:
            return new int[] { 0, data.getWidth(), data.getHeight() / 3, data.getHeight() };
        }
        return null;
    }

    private int[][] detectColor(Raster data, int type) {
        double[][] results = new double[3][256];

        // On detecte le cas du ciel (tiers superieur tout bleu). Si oui, on prend les 2 tiers inferieur

        int[] bounds = getBoundsImage(type, data);

        // int beginX = center ? data.getWidth() / 4 : 0;
        // int endX = (int) (center ? data.getWidth() * 0.75 : data.getWidth());
        // int beginY = center ? data.getHeight() / 4 : 0;
        // int endY = (int) (center ? data.getHeight() * 0.75 : data.getHeight());
        //
        int totalPoints = (bounds[1] - bounds[0]) * (bounds[3] - bounds[2]);

        HashMap<KeyColor, Integer> countByColor = new HashMap<KeyColor, Integer>();
        for (int x = bounds[0]; x < bounds[1]; x++) {
            for (int y = bounds[2]; y < bounds[3]; y++) {
                KeyColor key = new KeyColor(data.getSample(x, y, 0), data.getSample(x, y, 1), data.getSample(x, y, 2), 51);
                if (!countByColor.containsKey(key)) {
                    countByColor.put(key, 1);
                } else {
                    countByColor.put(key, countByColor.get(key) + 1);
                }
                for (int color = 0; color < 3; color++) {
                    results[color][data.getSample(x, y, color)]++;
                    // results[color][data.getSample(x, y, color) / 5]++;
                }
            }
        }

        int tempTotal[] = new int[3];
        int tempMax[] = new int[3];
        double[] moyennes = new double[3];
        int medianes[] = new int[3];
        int max[] = new int[3];
        // Regroupe par couleur complete
        // Tester avec l'element le plus present. Regrouper tous les 5 pour lisser les resultats
        for (int x = 00; x < 256; x++) {
            for (int color = 0; color < 3; color++) {
                // Resultats > 1%
                double value = results[color][x];
                if (tempTotal[color] < totalPoints / 2) {
                    tempTotal[color] += value;
                } else {
                    if (medianes[color] == 0) {
                        medianes[color] = x;
                    }
                }
                if (value / totalPoints >= 0.01) {
                    moyennes[color] += (value / totalPoints) * x;
                    if (tempMax[color] < value) {
                        tempMax[color] = (int) value;
                        max[color] = x;
                    }
                }
            }
        }

        // Calcul max par couleur
        int maxValue = 0;
        KeyColor maxKey = null;
        for (KeyColor key : countByColor.keySet()) {
            /* 3 cas : plus grand et non gris, gris et 3 fois plus, 3 fois moins mais non gris */
            if ((countByColor.get(key) > maxValue && !key.isGray())) {// || //
                // (key.isGray() && maxValue / countByColor.get(key) < 0.3) || //
                // (!key.isGray() && maxValue / countByColor.get(key) > 0.3)) {
                // On peut ajouter un test pour privilegier les couches differentes (pour eviter le gris), meme si un peu inf
                if (!key.isGray() || maxValue / countByColor.get(key) < 0.3) {
                    maxValue = countByColor.get(key);
                    maxKey = key;
                }
            }
        }

        // On arrondi
        // for (int color = 0; color < 3; color++) {
        // moyennes[color] = (moyennes[color] / 51) * 51 + ((moyennes[color] % 51 > 25) ? 51 : 0);
        // medianes[color] = (medianes[color] / 51) * 51 + ((medianes[color] % 51 > 25) ? 51 : 0);
        // }
        int[] average = new int[] { (int) moyennes[0], (int) moyennes[1], (int) moyennes[2] };

        return new int[][] { average, medianes, max, maxKey.toColor() };
    }

    // Arrondi au a la valeur superieur
    private int roundTo(int value, int pas) {
        return value % pas == 0 ? value : value + (pas - value % pas);
    }

    public String convertColorFromHexa(String color) {
        return null;
    }
}
