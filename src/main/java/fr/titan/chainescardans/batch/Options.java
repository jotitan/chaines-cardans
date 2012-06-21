package fr.titan.chainescardans.batch;

import org.kohsuke.args4j.Option;

/**
 * Created with IntelliJ IDEA.
 * User: Titan
 * Date: 21/06/12
 * Time: 00:20
 * To change this template use File | Settings | File Templates.
 */
public class Options {
    @Option(name = "-h",aliases = "--ftphost",required = true,usage = "Url du serveur ftp")
    private String ftpHost;

    @Option(name = "-u",aliases = "--user",required = true,usage = "User ftp")
    private String user;

    @Option(name = "-p",aliases = "--password",required = false,usage = "Password ftp")
    private String password;

    @Option(name = "-rd",aliases = "--remoteDirectory",required = false,usage = "Repertoire des photos sur le serveur ftp")
    private String remoteDirectory;

    @Option(name = "-d",aliases = "--directoryPhotos",required = true,usage = "Chemin des photos a traiter")
    private String photosDirectory;

    @Option(name ="-r",aliases = "--recursive",required = false,usage = "Scanne les sous repertoires")
    private boolean recursive = false;

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
}
