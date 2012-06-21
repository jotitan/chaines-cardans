package fr.titan.chainescardans.batch;


import org.kohsuke.args4j.CmdLineParser;

import java.io.File;
import java.util.ResourceBundle;

/**
 * Created with IntelliJ IDEA.
 * User: Titan
 * Date: 20/06/12
 * Time: 18:32
 * To change this template use File | Settings | File Templates.
 */
public class Main {

    static String irfanDir = "c:\\Program Files\\IrfanView\\i_view32.exe";
    public static String ftpHost = "";
    public static String ftpLogin = "";
    public static String ftpPass = "";
    public static String dirPhotos = "";
    public static String dirEspacePhotos = "";

    static{
        ResourceBundle r = ResourceBundle.getBundle("param");
        /*ftpHost = r.getString("ftpHost");
        ftpLogin = r.getString("ftpLogin");
        ftpPass = r.getString("ftpPass");
        dirPhotos = r.getString("dirPhotos");
        dirEspacePhotos = r.getString("dirEspacePhotos");*/
    }

    public static void main(String[] args) throws Exception{
        Options options = new Options();
        new CmdLineParser(options).parseArgument(args);

        System.out.println(options.getFtpHost() + " " + options.getUser());


        /*String dir = args[0];
        boolean tri = ("1".equals(args[1]));
        boolean bruteMode = ("1".equals(args[2]));
        boolean modeSubScan = ("1".equals(args[3]));

        ConvertisseurPhoto conv = new ConvertisseurPhoto();

        if(modeSubScan == true){
            conv.traiterRoot(dir, bruteMode, tri);
        }
        else{
            conv.traiterDir(new File(dir), tri, bruteMode);
        }

        new FtpUploader(ftpHost,ftpLogin,ftpPass,dirPhotos).uploadPhotos(conv.getDirToUpdate(), bruteMode, dir);
        System.out.println(conv.getUpdateScript(1));*/
    }
}
