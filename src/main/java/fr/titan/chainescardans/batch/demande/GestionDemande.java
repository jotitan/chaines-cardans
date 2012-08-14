package fr.titan.chainescardans.batch.demande;

import java.io.File;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;

import javax.xml.parsers.DocumentBuilderFactory;

import org.apache.log4j.Logger;
import org.w3c.dom.Document;
import org.w3c.dom.NodeList;

import fr.titan.chainescardans.batch.ftp.FtpUploader;


public class GestionDemande {
	private final String url = "http://vbaranzini.free.fr/v2.0/"; 
	private final String drive = new File("").getAbsolutePath().replaceAll("\\\\","/").split("/")[0] + "/Photos/";
	private Logger logger = Logger.getLogger("GestionDemande");
	private static final String patternPentecote = ".*[0-9]{4}_[A-Z0-9]+/.+$";
	private static final String patternJournee = ".*[0-9]{4}_[0-9]{2}_[0-9]{2}_[A-Z0-9_]+/.+$";
	private static final String patternDivers = ".*[0-9]{4}_[0-9]{2}_[0-9]{2}_DIVERS_[A-Z0-9_]+/.+$";
	
	public static void main(String[] args) {
		GestionDemande gd = new GestionDemande();
		List<Demande> list = gd.getXmlDemandes();
		gd.treatDemandes(list);
		gd.uploadListDemandes(list);
		gd.notifyUsers();
	}

	private class Demande{
		String path;
		String login;
		boolean error =  false;
		public Demande(String path,String login){
			this.path = path;
			this.login = login;
		}
		public String getLogin() {
			return login;
		}
		public void setLogin(String login) {
			this.login = login;
		}
		public String getPath() {
			return path;
		}
		public void setPath(String path) {
			this.path = path;
		}
		public String toString(){
			return "Photo " + path + " pour le compte " + login;
		}
		public boolean isError() {
			return error;
		}
		public void setError(boolean error) {
			this.error = error;
		}	

	}

	public List<Demande> getXmlDemandes(){
		List<Demande> l = new ArrayList<Demande>();
		String u = url + "photosToUpload?action=0";
		try{
			Document d = DocumentBuilderFactory.newInstance().newDocumentBuilder().parse(new URL(u).openStream());
			NodeList n = d.getElementsByTagName("photo");
			for(int i = 0 ; i < n.getLength() ; i++){
				l.add(new Demande(n.item(i).getAttributes().getNamedItem("path").getNodeValue(),n.item(i).getAttributes().getNamedItem("login").getNodeValue()));
			}
			logger.info("Nombres de demande : " +  l.size());
			return l;
		}catch(Exception e){
			return l;
		}
	}

	/* Recherche les photos et modifie les chemins */
	public void treatDemandes(List<Demande> list){
		for(Demande d : list){
			// On trouve le repertoire de demande
			try{
				logger.info("Recherche de photo : " + drive + getDir(d.getPath()) + d.getPath());
			if(new File(drive + getDir(d.getPath()) + d.getPath()).exists()){
				d.setPath(drive + getDir(d.getPath()) + d.getPath().replace("/ld/","/hd/"));
			}
			else{
				d.setPath(null);
			}
			}catch(Exception e){
				d.setError(true);
				logger.error("Erreur dans qualification du chemin");
			}
		}
	}

	private String getDir(String path)throws Exception{
		String dir = "";
		if(path == null){
			return dir;
		}
		if(path.matches(patternPentecote)){
			return "PENTECOTE/";
		}
		if(path.matches(patternJournee)){
			return "JOURNEE/";
		}
		if(path.matches(patternDivers)){
			return "DIVERS/";
		}
		throw new Exception("");
	}
	
	/* Envoie les differentes photos par ftp */
	public void uploadListDemandes(List<Demande> l){
		try{
			/*logger.info("Upload des demandes (" + l.size() + ")");
			logger.info("Param : " + ftpHost + " , " + ftpLogin + " , " + dirEspacePhotos);
			FtpUploader ftp = new FtpUploader(ftpHost,ftpLogin,ftpPass,dirEspacePhotos);
			for(Demande d : l){
				if(d.getPath()!=null && !d.isError()){
					try{
						ftp.uploadPhoto(d.getPath(), d.getLogin() + "/uploaded/");
					}catch(Exception e){
						logger.info("Erreur upload " + d.getPath());
					}
				}
			} */
		}catch(Exception e){
			logger.info("Erreur creation connexion ftp");
			return;
		}
	}
	
	public void notifyUsers(){
		try{
			new URL(url + "photosToUpload?action=1").openStream();
			logger.info("Notification effectuee");
		}catch(Exception e){
			
		}
	}

}
