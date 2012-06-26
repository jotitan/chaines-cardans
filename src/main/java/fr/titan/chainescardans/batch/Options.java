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
}
