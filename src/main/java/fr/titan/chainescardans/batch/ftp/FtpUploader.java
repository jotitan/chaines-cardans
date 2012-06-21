package fr.titan.chainescardans.batch.ftp;

import java.io.File;
import java.util.List;
import org.apache.log4j.Logger;

public class FtpUploader {
	private FtpManager ftpUploader;
	private Logger logger = Logger.getLogger("FtpUploader");
	private String ftpDir;
	
	public FtpUploader(String ftpPath, String login, String pass,String ftpDir)throws Exception{
		ftpUploader = new FtpManager(ftpPath,login,pass);
		this.ftpDir = ftpDir;
	}
	
	public void uploadPhotos(List<String> dirToUpload, boolean bruteMode,String rootDir){
		logger.info("Traitement de l'upload ftp...");
		for(String s : dirToUpload){
			try{
				File f = new File(s);
				ftpUploader.uploadDirectory(f, ftpDir + f.getAbsolutePath().substring(rootDir.length()).replace("\\", "/"), bruteMode);
			}catch(Exception e){
				logger.error("Erreur ftp dir " + s + " : " + e.getMessage());
			}
		}
	}

	public void uploadPhoto(String photo,String to){
		File f = new File(photo);
		try{
			ftpUploader.uploadFile(photo, ftpDir + to + f.getName());
			logger.info("Photo " + photo + " uploade vers " + to);
		}catch(Exception e){
			logger.error("Erreur ftp " + photo + " : " + e.getMessage());
		}
	}
	
	public FtpManager getFtpUploader() {
		return ftpUploader;
	}
	
	
}
