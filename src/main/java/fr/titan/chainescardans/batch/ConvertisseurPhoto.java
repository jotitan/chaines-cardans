package fr.titan.chainescardans.batch;

import java.io.File;
import java.io.FileFilter;
import java.io.FilenameFilter;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.*;

import fr.titan.chainescardans.batch.ftp.FtpManager;
import org.apache.log4j.Logger;

import com.drew.imaging.jpeg.JpegMetadataReader;
import com.drew.metadata.Metadata;
import com.drew.metadata.exif.ExifDirectory;

public class ConvertisseurPhoto {
	private final String irfanDir;
	private Logger logger = Logger.getLogger("ConvertisseurPhoto");
	private ArrayList<String> dirToUpdate = new ArrayList<String>();
	private List<String> updateScript = new ArrayList<String>();
	private int bigHeight;
	private int lowHeight;
    private FtpManager ftpManager;

    public ConvertisseurPhoto(int bigHeight,int lowHeight){
        ResourceBundle r = ResourceBundle.getBundle("param");
        irfanDir = r.getString("software.dir");
        this.bigHeight = bigHeight;
        this.lowHeight = lowHeight;
    }

    public void setFtpManager(FtpManager ftpManager){
        this.ftpManager = ftpManager;
    }


    private void addSynchroPhoto(String dir,List<String> list){
        logger.info("0-Ajout de " + dir);
        list.add(dir + "/sd");
        list.add(dir + "/ld");
    }

    /**
     * Cherche les repertoires a synchroniser
     * @param localFolder : repertoire local a synchroniser
     * @param remoteFolder : repertoire distant a synchroniser
     * @param typeSynchro : type de synchro. 0 : 1er niveau, 1 : 2nd niveau, 2 : compte le nombre de fichier, 3 : compare les noms
     *
     */
	public List<String> getSynchroDirectories(String root,String localFolder,String remoteFolder,int typeSynchro){
        List<String> folders = new ArrayList<String>();
        File[] dirs = new File(localFolder).listFiles(new FileFilter(){
            public boolean accept(File f) {
                return f.isDirectory();
            }
        });
        for(File d : dirs){
            if("AUTRES".equals(d.getName())){
                return folders;
            }
            if(!new File(d.getAbsolutePath() + "\\hd").exists()){
                // Pas de rep hd, on descend dans les fils
                folders.addAll(getSynchroDirectories(root,d.getAbsolutePath(), remoteFolder, typeSynchro));
            }
            else{
                // On ne fait le traitement que si un repertoire hd existe et que les images sd et ld ont ete crees
                if(!new File(d.getAbsolutePath() + "\\sd").exists() || !new File(d.getAbsolutePath() + "\\ld").exists()){
                    // On ne fait rien
                }
                else{
                    // On test si le repertoire existe
                    String testName = (remoteFolder + d.getAbsolutePath().replace(root,"")).replaceAll("\\\\","/");
                    if(!ftpManager.isDirectoryExist(testName)){
                        // On verifie si le repertoire existe sur le serveur distant
                        addSynchroPhoto(d.getAbsolutePath(),folders);
                    }
                    else{
                        // Verif de premier niveau, on verifie que le repertoire sd et ld sont present
                        if(typeSynchro > 0){
                            if(!ftpManager.isDirectoryExist(testName + "/sd") || !ftpManager.isDirectoryExist(testName + "/ld")){
                                addSynchroPhoto(d.getAbsolutePath(),folders);
                            }
                            else{
                                // On verifie que le nombre de photos est le meme
                                if(typeSynchro == 2){
                                    FilenameFilter filter = new FilenameFilter() {
                                        @Override
                                        public boolean accept(File dir, String name) {
                                            return !name.contains(".db");
                                        }
                                    };
                                    logger.info("Nb rep " + d.getAbsolutePath() + "\\sd " + (new File(d.getAbsolutePath() + "\\sd").list(filter).length+2));

                                    if((new File(d.getAbsolutePath() + "\\sd").list(filter).length+2) != ftpManager.getNbFiles(testName + "/sd")){
                                        addSynchroPhoto(d.getAbsolutePath(),folders);
                                    };
                                }
                            }
                        }
                    }
                }
            }
        }
        return folders;
    }

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
//            logger.info("Envoi simple du repertoire " + dir + " par ftp");
//			dirToUpdate.add(dir + "\\sd");
//            dirToUpdate.add(dir + "\\ld");
//            return;
//        }
		
		if(!new File(dir + "\\sd").exists() && !new File(dir + "\\ld").exists()){
            updateScript.add(new File(dir).getName());
        }
		String name = new File(dir).getName().toLowerCase();
		if(!new File(dir + "\\sd").exists()){
			if(!new File(dir + "\\sd").mkdir()){
                logger.error("Probleme lors de la creation du repertoire sd");
            }
			convertFiles(files, dir + "\\sd", bigHeight,name);
		}
		else{
			if(bruteMode){
				convertFiles(files, dir + "\\sd", bigHeight,name);
			}
		}
		if(!new File(dir + "\\ld").exists()){
			if(!new File(dir + "\\ld").mkdir()){
                logger.error("Probleme lors de la creation du repertoire ld");
            }
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
        Collections.addAll(files,list);

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
		logger.info("...Debut de conversion des fichiers de " + outDir);
		dirToUpdate.add(outDir);
		int count = 0;
		for(File f : files){
			String fn = name + count++ + ".jpg";
			File renameFile = new File(f.getParent() + "\\" + fn);
			if(!f.renameTo(renameFile)){
                logger.error("Erreur lors de la creation de " + renameFile);
            }
			
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
	            s.append("INSERT INTO media VALUES(null,").append(typeSortie).append(",'','").append(us).append("',").append(formatDate(us)).append(")\n");
	        }
	        return s.toString();
	    }

	public ArrayList<String> getDirToUpdate() {
		return dirToUpdate;
	}

}
