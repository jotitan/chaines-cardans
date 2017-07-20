package fr.titan.chainescardans.batch;

import org.kohsuke.args4j.Option;

public class Options {
    @Option(name = "-h",aliases = "--ftphost",required = true,usage = "Url du serveur ftp")
    private String ftpHost = "";

    @Option(name = "-u",aliases = "--user",required = true,usage = "User ftp")
    private String user = "";

    @Option(name = "-p",aliases = "--password",required = false,usage = "Password ftp")
    private String password = "";

    @Option(name = "-rd",aliases = "--remoteDirectory",required = false,usage = "Repertoire des photos sur le serveur ftp")
    private String remoteDirectory = "";

    @Option(name = "-d",aliases = "--directoryPhotos",required = true,usage = "Chemin des photos a traiter")
    private String photosDirectory = "";

    @Option(name ="-r",aliases = "--recursive",required = false,usage = "Scanne les sous repertoires")
    private boolean recursive = false;

    @Option(name ="-s",aliases = "--synchro",required = false,usage = "Synchro repertoire uniquement. 0 : 1er niveau, 1 : 2nd niveau, 2 : nb fichiers ")
    private Integer synchro = null;

    @Option(name ="-bh",aliases = "--bigHeight",required =false,usage="Hauteur de la grande image")
    private int bigHeight = 600;

    @Option(name ="-lh",aliases = "--lowHeight",required =false,usage="Hauteur de la petite image")
    private int lowHeight = 100;

    @Option(name ="-wl",aliases = "--webLogin",required =false,usage="Login du compte utilisateur web")
    private String webLogin = "";

    @Option(name ="-wp",aliases = "--webPass",required =false,usage="password du compte utilisateur web")
    private String webPass = "";

    @Option(name ="-fl",aliases = "--flickrLogin",required =false,usage="Login du compte flickr")
    private String loginFlickr = "";

    @Option(name ="-fp",aliases = "--flickrPass",required =false,usage="password du compte flickr")
    private String passFlickr = "";

    @Option(name ="-fk",aliases = "--flickrKey",required =false,usage="cle du compte flickr")
    private String keyFlickr = "";


    public String getFtpHost() {
        return ftpHost;
    }

    public String getUser() {
        return user;
    }

    public String getPassword() {
        return password;
    }

    public String getRemoteDirectory() {
        return remoteDirectory;
    }

    public String getPhotosDirectory() {
        return photosDirectory;
    }

    public boolean isRecursive() {
        return recursive;
    }

    public int getBigHeight() {
        return bigHeight;
    }

    public int getLowHeight() {
        return lowHeight;
    }

    public Integer getSynchro() {
        return synchro;
    }

    public String getWebLogin() {
        return webLogin;
    }

    public String getWebPass() {
        return webPass;
    }

    public String getLoginFlickr() {
        return loginFlickr;
    }

    public String getPassFlickr() {
        return passFlickr;
    }

    public String getKeyFlickr() {
        return keyFlickr;
    }
}
