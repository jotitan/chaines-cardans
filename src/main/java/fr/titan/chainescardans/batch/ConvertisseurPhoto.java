package fr.titan.chainescardans.batch;

import java.io.File;
import java.io.FileFilter;
import java.io.FilenameFilter;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.Date;
import java.util.List;
import org.apache.log4j.Logger;

import com.drew.imaging.jpeg.JpegMetadataReader;
import com.drew.metadata.Metadata;
import com.drew.metadata.exif.ExifDirectory;

public class ConvertisseurPhoto {
	private final String irfanDir = "c:\\Program Files\\IrfanView\\i_view32.exe";
	private Logger logger = Logger.getLogger("ConvertisseurPhoto");
	private ArrayList<String> dirToUpdate = new ArrayList<String>();
	private List<String> updateScript = new ArrayList<String>();
	private final int bigHeight = 600;
	private final int lowHeight = 100;
	
	public void traiterRoot(String root,boolean bruteMode,boolean tri){
		File[] dirs = new File(root).listFiles(new FileFilter(){
			public boolean accept(File f) {
				return f.isDirectory();
			}
		});
		for(File d : dirs){
			traiterDir(d, tri, bruteMode);			
		}
	}

	public void traiterDir(File d,boolean tri,boolean bruteMode){
		logger.info("Traitement du repertoire " + d + "\\hd");
		if("AUTRES".equals(d.getName())){
			return;
		}
		if(!new File(d.getAbsolutePath() + "\\hd").exists()){
			traiterRoot(d.getAbsolutePath(), bruteMode, tri);
			return;
		}
		// On verifie si les photos ont deja ete compressee ou si le brute mode est active
		String dir = d.getAbsolutePath();
		if(new File(dir + "\\sd").exists() && new File(dir + "\\ld").exists()){
			return;
		}
		List<File> files = chargerPhotos(dir + "\\hd"); 		
		if(tri){
			trierPhotos(files);
		}
		convertCC(files,dir,bruteMode);
	}
	
	private void convertCC(List<File> files,String dir,boolean bruteMode){
//		if(bruteMode == false && new File(dir + "\\sd").exists() && new File(dir + "\\ld").exists()){
//            logger.info("Envoi simple du r�pertoire " + dir + " par ftp");
//			dirToUpdate.add(dir + "\\sd");
//            dirToUpdate.add(dir + "\\ld");
//            return;
//        }
		
		if(!new File(dir + "\\sd").exists() && !new File(dir + "\\ld").exists()){
            updateScript.add(new File(dir).getName());
        }
		String name = new File(dir).getName().toLowerCase();
		if(!new File(dir + "\\sd").exists()){
			new File(dir + "\\sd").mkdir();
			convertFiles(files, dir + "\\sd", bigHeight,name);
		}
		else{
			if(bruteMode){
				convertFiles(files, dir + "\\sd", bigHeight,name);
			}
		}
		if(!new File(dir + "\\ld").exists()){
			new File(dir + "\\ld").mkdir();
			convertFiles(files, dir + "\\ld", lowHeight,name);
		}
		else{
			if(bruteMode){
				convertFiles(files, dir + "\\ld", lowHeight,name);
			}
		}

	}

	private List<File> chargerPhotos(String dir){
		File[] list =  new File(dir).listFiles(new FilenameFilter(){
			public boolean accept(File arg0, String name) {
				return name.toLowerCase().endsWith(".jpg") || name.toLowerCase().endsWith(".jpeg");
			}
		});
		ArrayList<File> files = new ArrayList<File>();
		for(File file : list){
			files.add(file);
		}
		return files;

	}

	private void trierPhotos(List<File> files){
		logger.info("...Tri de photos");
		Collections.sort(files,new Comparator<File>(){
			public int compare(File f1, File f2) {
				try{
					Metadata meta1 = JpegMetadataReader.readMetadata(f1);
					Metadata meta2 = JpegMetadataReader.readMetadata(f2);
					Date d1 = ((ExifDirectory)meta1.getDirectoryIterator().next()).getDate(ExifDirectory.TAG_DATETIME_ORIGINAL);
					Date d2 = ((ExifDirectory)meta2.getDirectoryIterator().next()).getDate(ExifDirectory.TAG_DATETIME_ORIGINAL);
					return d1.compareTo(d2);
				}catch(Exception e){return 1;}
			}
		});
	}

	private void convertFiles(List<File> files,String outDir,int height,String name){
		logger.info("...D�but de conversion des fichiers de " + outDir);
		dirToUpdate.add(outDir);
		int count = 0;
		for(File f : files){
			String fn = name + count++ + ".jpg";
			File renameFile = new File(f.getParent() + "\\" + fn);
			f.renameTo(renameFile);
			
			final String convert = "\"" + irfanDir + "\" \"" + renameFile.getAbsolutePath() + "\" /jpgq=80 /resize=(0," + height + ") /resample /aspectratio /convert=\"" + outDir + "\\" + fn + "\""; 
			logger.info("......Traitement de l'image " + f.getName());
			try{
				Process p = Runtime.getRuntime().exec(convert);
				p.waitFor();
			}catch(Exception e){
				logger.info(e.getMessage());
			}
		}
		logger.info("...Fin de conversion des fichiers");
	}
	
	private static String formatDate(String nameDir){
        DateFormat in = new SimpleDateFormat("yyyy_MM_dd");
        DateFormat out = new SimpleDateFormat("yyyy-MM-dd");
        try{
            return out.format(in.parse(nameDir.substring(0,10)));
        }catch(Exception e){
            return "";
        }
       
    }
	
	  public String getUpdateScript(int typeSortie){
	        StringBuilder s = new StringBuilder();
	        for(String us : updateScript){
	            s.append("INSERT INTO MEDIA VALUES(null," + typeSortie + ",'','" + us + "'," + formatDate(us) + ")\n");
	        }
	        return s.toString();
	    }

	public ArrayList<String> getDirToUpdate() {
		return dirToUpdate;
	}

	public void setDirToUpdate(ArrayList<String> dirToUpdate) {
		this.dirToUpdate = dirToUpdate;
	}

}
