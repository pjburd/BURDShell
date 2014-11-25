<?php
/*
    BURDShell: Developer platform shell
    Copyright (C) 2014  Paul Burden

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class BURDShell_OSx extends BURDShell_interface {

	public $os_version = "OSX 10.9";

	

	public function network_dynamic() 
	{
		
		if (Config::$virtual_machine == TRUE)
		{
//Not allowed in OSx environment - Maybe different
//		    $out_lines[] = exec("sudo cp /etc/network/interfaces.dynamic /etc/network/interfaces");    	    
//			$this->print_output($out_lines);	 
			   
//		    $this->network_restart();   
			$this->print_line("[INFO] Command disabled for ".$this->os_version);	

	    }
	    else
	    {
			$this->print_line("[INFO] Shell is not in virtual machine environment.");	
	    }
	}
	
	public function network_static() 
	{
		if (Config::$virtual_machine == TRUE)
		{
//Not allowed in OSx environment - Maybe different
//		    $out_lines[] = exec("sudo cp /etc/network/interfaces.static /etc/network/interfaces");   
//			$this->print_output($out_lines);
	 
//		    $this->network_restart();   
			$this->print_line("[INFO] Command disabled for ".$this->os_version);	

	    }
	    else
	    {
			$this->print_line("[INFO] Shell is not in virtual machine environment.");	
	    }
	    
	}
	
	public function network_status() {
		exec("/sbin/ifconfig", $out_lines);
		$this->print_output($out_lines);
	}
	
	public function network_restart() {		
		if (Config::$virtual_machine == TRUE)
		{
//NOT Allowed yet


//			echo "\nRestarting network:";

//$ sudo ifconfig en0 down
//$ sudo ifconfig en0 up				<--- Use  Config::$network_interface

// Use en1 for your AirPort card
			
//		    exec("sudo ifconfig en0 down", $out_lines);
//		    exec("sudo ifconfig en0 up", $out_lines);
//			$this->print_output($out_lines);
			$this->print_line("[INFO] Command disabled for ".$this->os_version);	

	    }
	    else
	    {
			$this->print_line("[INFO] Shell is not in virtual machine environment.");	
	    }
	    			    
	}

	public function get_ip_address() {
		 $out_line = exec("ipconfig getifaddr ". Config::$network_interface);
		 return $out_line;
	}

	public function webserver_status() 
	{
	    exec("ps aux | grep httpd", $out_lines);	    
		$this->print_output($out_lines);
	    exec("apachectl configtest", $out_lines);	    
		$this->print_output($out_lines);	 		
	}
	
	public function webserver_sites() 
	{
	    exec("apachectl -S", $out_lines);	    
		$this->print_output($out_lines);	 		
	}
	
	public function webserver_restart() 
	{
		$this->print_output($this->admin_exec("apachectl restart"));	 			   	 			
		$this->print_line("[INFO] Webserver restarted.");
	}

	public function site_list() 
	{
	    exec("ls -l " . Config::$virtualhost_dir, $out_lines);	    
		$this->print_output($out_lines);	 		
	}

	public function site_help()
	{		
		$user_input = $this->project_prompt("What is the site domain?");

		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{

			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
				
					//Make sure the file exists
					if (!file_exists( Config::$virtualhost_dir.$user_input.".conf")) 
					{
						$this->print_line("[ERROR] site domain '".$user_input."' does not exist.");			
					}
					else
					{						
						echo "#############\n";
						echo "# site help #\n";
						echo "#############\n";		
						$this->print_line("echo \"".$this->get_ip_address()."  ".$user_input."\" >> /etc/hosts");
						$this->print_line("OR");
						$this->print_line("echo \"127.0.0.1  ".$user_input."\" >> /etc/hosts");
					}
				}
			}
		}
	}
	
	public function site_delete()
	{			
		$user_input = $this->project_prompt("What is the site domain?");

		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{

			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
				
					//Make sure the file exists
					if (!file_exists( Config::$virtualhost_dir.$user_input.".conf")) 
					{
						$this->print_line("[ERROR] site domain '".$user_input."' does not exist.");			
					}
					else
					{					
						//Delete config						
						$this->print_output($this->admin_exec("rm ". Config::$virtualhost_dir.$user_input.".conf", FALSE));
							
						//Restart webserver gracefully
						$this->print_output($this->admin_exec("apachectl graceful"));
						
						//reload server
						$this->print_line("[INFO] Site deleted :)");
						$this->print_line("[IMPORTANT] Update host machine /etc/hosts file to remove redundant site.");
						
					}
				}
			}		
		}
	}
			
	public function site_create() 
	{			
		$user_input = $this->project_prompt("What is the site domain?");
	
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
			
					//Make sure the file exists
					if (!file_exists( Config::$virtualhost_dir.$user_input.".conf")) 
					{
						$this->print_line("Creating site domain '".$user_input."'...");			
						
						//Read template into variable
						$template = file_get_contents(Config::$shell_folder.'/BURDShell/templates/osx-apache2-virtualhost.txt');
	
						// Change template key names
						$template = preg_replace("/\[DOMAINNAME\]/", $user_input, $template);	
						$template = preg_replace("/\[SITE\_FOLDER\]/", Config::$site_dir, $template);	
									
						// Save file
						file_put_contents( Config::$virtualhost_dir.$user_input.".conf", $template);		// Mac requires .conf at end
						
						// Set up default structure						
						if (file_exists(Config::$site_dir.$user_input)) 
						{
							$this->print_line("Site folder '".$user_input."' exists");			
						}
						else
						{
							$this->print_line("Creating site folder '".$user_input."' scaffold...");
							
							$this->print_output($this->admin_exec("mkdir ".Config::$site_dir.$user_input, FALSE));
							$this->print_output($this->admin_exec("mkdir ".Config::$site_dir.$user_input."/public", FALSE));
							$this->print_output($this->admin_exec("mkdir ".Config::$site_dir.$user_input."/private", FALSE));
														
							$template = file_get_contents(Config::$shell_folder.'/BURDShell/templates/site-index-file.txt');
							$template = preg_replace("/\[PROJECT\]/", $user_input, $template);						
										
							file_put_contents(Config::$site_dir.$user_input."/public/index.html", $template);
							
							$this->print_output($this->admin_exec("chown -R ".Config::$shell_user." ".Config::$site_dir.$user_input, FALSE));
							$this->print_output($this->admin_exec("chgrp -R ".Config::$shell_group." ".Config::$site_dir.$user_input, FALSE));
								
						}
						

						
						// Enable server
						$this->print_output($this->admin_exec("apachectl graceful"));						

						$this->print_line("[INFO] Site created :)");
						$this->print_line("[IMPORTANT] Update host machine /etc/hosts file to make site available.");
						$this->print_line("Run the following:");						
						$this->print_line("echo \"".$this->get_ip_address()."  ".$user_input."\" >> /etc/hosts");
						$this->print_line("OR");
						$this->print_line("echo \"127.0.0.1  ".$user_input."\" >> /etc/hosts");
					} 
					else 
					{
						$this->print_line("[ERROR] site domain '".$user_input."' already exists. Run 'webserver sites' for further info.");
					}			
				}
						
			}

		}
	}
	
	public function svn_list() 
	{		 
	    exec("ls -l ".Config::$shell_folder."/svn/", $out_lines);	    
		$this->print_output($out_lines);	 	
	}
	


	public function svn_help() 
	{		 
		$user_input = $this->project_prompt("Which site to view svn help for?");

		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
						echo "############\n";
						echo "# svn help #\n";
						echo "############\n";
						echo "[INFO]cd /in/to/work/folder/ and then run either following:";							
						echo "\n\n## Checkout project ##\nsvn co svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input."/trunk ".$user_input." --username ".Config::$shell_user;
						echo "\n\n## Export project ##\nsvn export svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input."/trunk ".$user_input." --username ".Config::$shell_user;
						echo "\n\n## Show history ##\nsvn log svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input." --username ".Config::$shell_user." --no-auth-cache";
						echo "\n\n## Tag release 1.0 sample ##\nsvn copy svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input."/trunk svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input."/tags/1.0 --username ".Config::$shell_user." -m \"Release 1.0\"";						
						echo "\n\n## Delete tag sample ##\nsvn delete svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input."/tags/1.0 --username ".Config::$shell_user." -m \"Deleted release 1.0\"";						
						echo "\n\n## Create branch 'prototype' sample ##\nsvn copy svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input."/trunk svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input."/branches/prototype --username ".Config::$shell_user." -m \"Created branch 'prototype'\"";						
						echo "\n\n## Delete branch 'prototype' sample ##\nsvn delete svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input."/branches/prototype --username ".Config::$shell_user." -m \"Deleted branch 'prototype'\"";
						echo "\n\n## Commit changes ##\nsvn commit";
						echo "\n\n## Show status ##\nsvn status";
						echo "\n\n";						
					}
				}
			}
		}
	}	
	
	
	
	public function svn_create() 
	{		 
		$user_input = $this->project_prompt("Which site to create repo?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					$this->print_line("Making site repo...");					
					if (file_exists(Config::$shell_folder."/svn/".$user_input))
					{
						$this->print_line("[ERROR] Site repo already exists.");
					} 
					else
					{
					    exec("mkdir ".Config::$shell_folder."/svn/".$user_input, $out_lines);	   
						$this->print_output($out_lines);
						
						//Create repo
					    exec(Config::$svn_bin_path."svnadmin create ".Config::$shell_folder."/svn/".$user_input, $out_lines);	   
						$this->print_output($out_lines);	 											     
	
						exec("svn mkdir -m\"Created basic directory structure\" file:///".Config::$shell_folder."/svn/".$user_input."/trunk file:///".Config::$shell_folder."/svn/".$user_input."/branches file:///".Config::$shell_folder."/svn/".$user_input."/tags", $out_lines);						
						$this->print_output($out_lines);	 											     
	
						$this->svn_security($user_input);
	
						$this->print_line("Site repo created.");
						
						$this->print_line("Checkout project:\nsvn co svn://".Config::$shell_host_domain.Config::$shell_folder."/svn/".$user_input."/trunk ".$user_input." --username ".Config::$shell_user);

					}
					
				}
			}
			
		}
		
	}

	public function svn_delete() 
	{		 
		$user_input = $this->project_prompt("Which site to delete repo?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
						$this->print_line("Deleting site repo...");
					    exec("rm -Rf ".Config::$shell_folder."/svn/".$user_input, $out_lines);	   
						$this->print_output($out_lines);	 
						$this->print_line("Site repo deleted.");					
					}
				
				}
			}
		}
	}
	

	public function svn_history() 
	{		 
		$user_input = $this->project_prompt("Which site to view repo history?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
					    exec(Config::$svn_bin_path."svnlook history ".Config::$shell_folder."/svn/".$user_input." --show-ids", $out_lines);	   
						$this->print_output($out_lines);	 
					}
				}
			}
		}
	}	

	public function svn_log() 
	{		 
		$user_input = $this->project_prompt("Which site to view repo history?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
						$revision = "";
						if (count($this->directive['args']) == 3)
						{	
							$revision = " -r ".$this->directive['args'][2];
						}
						
						if (empty($revision))
						{
						    exec(Config::$svn_bin_path."svnlook youngest ".Config::$shell_folder."/svn/".$user_input, $out_lines);	   
							
							$this->print_line("svn log");
							$this->print_line("=======");
							$this->print_line("Youngest revision:".$out_lines[0]."\n");
						}						
						$out_lines = array();			
						
						if ($revision) 
						{			
							$this->print_line("Revision:".$this->directive['args'][2]."\n");
						}
					    exec(Config::$svn_bin_path."svnlook log".$revision." ".Config::$shell_folder."/svn/".$user_input, $out_lines);	   
						$this->print_output($out_lines);	 
						

					}
				}
			}
		}
	}		
	
	public function svn_revision() 
	{		 

		$user_input = $this->project_prompt("Which site to view repo history revision?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
						if (count($this->directive['args']) == 3)
						{
							$revision = $this->directive['args'][2];
						}
						else
						{					
							echo "Which revision do you wish to view? (shell hint:'svn history')\n";
							$revision = trim(fgets(STDIN));	
						}
						
						exec(Config::$svn_bin_path."svnlook changed ".Config::$shell_folder."/svn/".$user_input." --revision ".$revision, $out_lines);	   
						if (count($out_lines))
					    {
							$this->print_line("--- FILES CHANGED ---");	
					    }
						$this->print_output($out_lines);	 
						
						$out_lines = array();
					    exec(Config::$svn_bin_path."svnlook log ".Config::$shell_folder."/svn/".$user_input." --revision ".$revision, $out_lines);	   
					    
					    if (count($out_lines))
					    {
							$this->print_line("--- LOG MESSAGE ---");	
					    }
						$this->print_output($out_lines);											
					}
				}
			}
		}
	}
	
	public function svn_users() 
	{		 
		$user_input = $this->project_prompt("Which site to view repo history?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
					    exec("cat ".Config::$shell_folder."/svn/".$user_input."/conf/passwd", $out_lines);	
					    
						$this->print_output($out_lines, TRUE);	 
					}
				}
			}
		}
	}	

	public function svn_user() 
	{		 
		$user_input = $this->project_prompt("Which site to view repo history?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
						if (count($this->directive['args']) == 3)
						{
							$svn_user = $this->directive['args'][2];
						}
						else
						{					
							echo "Which user to look up permissions? (shell hint:'svn users')\n";
							$svn_user = trim(fgets(STDIN));	
						}
						//Read in authz
						
					    exec("cat ".Config::$shell_folder."/svn/".$user_input."/conf/authz", $out_lines);
					    
					    $tidy_output = $this->tidy_flat_output($out_lines);
					    
					    
					    // Search for 'username entries'
					    $access_roles = array();					// aka 'alias' in svn conf
					    foreach($tidy_output as $out_line)
					    {				    	
						    if (preg_match("/[\=]*".quotemeta($svn_user)."/", $out_line))
						    {
								$elements = explode("=", $out_line);
								$access_roles[] = trim($elements[0]);
								// Now saerch for find directory they allowed
								
						    }
					    }
					    if (count($access_roles)) 
					    {
							    				    
						    // Find directories and rw
						    $permissions = array();					// aka 'grants' in svn conf
						    $repo_folders = array();
						    
						    foreach($access_roles as $access_role)
						    {
						    	$ctr = 0;
						    	foreach($tidy_output as $out_line)
								{
									if (preg_match("/\@".quotemeta($access_role)."/", $out_line))
								    {
										$elements = explode("=", $out_line);
										$permissions[] = trim($elements[1]);
										
										
										// Now locate rep folder
										$ctr_tmp = $ctr - 1;			// Mark current index of output
										while ($ctr_tmp > 0) 
										{
											if (!preg_match("/\[*\]/", $tidy_output[$ctr_tmp]))
											{
												$ctr_tmp--;
											}
											else
											{
												$repo_folders[] = trim($tidy_output[$ctr_tmp]);	// Get the line above found grant above
												break;		// Lets exit this 'locate repo folder' loop 
											}
										}
								    }
								    $ctr++;
								}
						    }
						    
						    //Print out permissions
						    $permissions_output = array();
							$ctr = 0;
							if (is_array($repo_folders))
							{
								foreach ($repo_folders  as $repo_folder)
								{
									$permissions_output[] = $access_roles[$ctr]." ".$repo_folder." ".$permissions[$ctr];
									$ctr++;
								}
							}					    
							$this->print_output($permissions_output);	 
						
					    }	
					    else
					    {
						    $this->print_line('[ERROR] SVN user not found.');
					    }
					}
				}
			}
		}
	}	

	public function svn_grants() 
	{		 
		$user_input = $this->project_prompt("Which site to view repo history?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
					    exec("cat ".Config::$shell_folder."/svn/".$user_input."/conf/authz", $out_lines);	
					    
						$this->print_output($out_lines, TRUE);	 
					}
				}
			}
		}
	}	


	public function svn_security($override_user_input="")
	{
		$user_input = $this->project_prompt("Which site repo to setup security?", $override_user_input);
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
  					    //Check if a security line exists
						if ($this->line_exists("anon-access = none", Config::$shell_folder."/svn/".$user_input."/conf/svnserve.conf"))
						{
							$this->print_line("[ERROR] site project repo already has security setup.");	
						} 
						else
						{
							// Safe to setup security
							/*
								[/svn/PROJECTFOLDER/conf/svnserve.conf]
								anon-access = none
								auth-access = write
								password-db = passwd
							*/
							exec("sed -i .bk 's/^# auth\-access \= write/anon\-access \= none\\nauth\-access \= write/' ".Config::$shell_folder."/svn/".$user_input."/conf/svnserve.conf");
							exec("sed -i .bk 's/^# password\-db \= passwd/password\-db \= passwd/' ".Config::$shell_folder."/svn/".$user_input."/conf/svnserve.conf");
	
							/*
								[/svn/PROJECTFOLDER/conf/passwd]
								sysadmin = Password1
							*/
							$security_lines = Config::$shell_user." = ".Config::$shell_pass."\n";
							file_put_contents(Config::$shell_folder."/svn/".$user_input."/conf/passwd", $security_lines, FILE_APPEND | LOCK_EX);
							
							/*
								[/svn/PROJECTFOLDER/conf/authz]
								allaccess = sysadmin
								[/]
								@allaccess = rw
							*/							
							$security_lines = "allaccess = ".Config::$shell_user."\n[/]\n@allaccess = rw\n";
							file_put_contents(Config::$shell_folder."/svn/".$user_input."/conf/authz", $security_lines, FILE_APPEND | LOCK_EX);
							
							$this->print_line("Site repo security setup successfully.");

						}
					}
				}
			}
		}		
	}

	public function svn_serve() 
	{		 
	    exec(Config::$svn_bin_path."svnserve -d", $out_lines);	    
		$this->print_output($out_lines);	 	
	}
	
	public function app_list()
	{
	    exec("ls -l ".Config::$app_folder."/", $out_lines);	    
		$this->print_output($out_lines);	 	

	}
	
	public function app_install()
	{		
//!ENHANCEMENT: Convert these series of 'site' if check questions to a common function that returns a an array of errors.

		$user_input = $this->project_prompt("What is the site domain?");
		$out_lines = array();

		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{

			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
				
					//Make sure the file exists
					if (!file_exists(Config::$virtualhost_dir.$user_input.".conf")) 
					{
						$this->print_line("[ERROR] site domain '".$user_input."' does not exist.");			
					}
					else
					{
						if ($this->_database_exists($user_input)) 
						{
							$this->print_line("[ERROR] app database '".$user_input."' already exists.");			
						}
						else
						{							
							if (count($this->directive['args']) == 3)
							{
								$app_name = $this->directive['args'][2];
							}
							else
							{					
								echo "Which app do you wish to install? (Currently only 'phpMyAdmin' and 'websvn')\n";
								$app_name = trim(fgets(STDIN));	
							}
							
							
							if (!$this->allowed_app($app_name))
							{
								$this->print_line("[ERROR] app '".$app_name."' is not allowed.");	
							}
							else
							{
								$this->print_line("[INFO] app '".$app_name."' is allowed.");							
									
								$app_versions = $this->get_app_files($app_name);
								$total_app_versions = count($app_versions);
								
								if ($total_app_versions >= 2)
								{
									$this->print_line("[ERROR] Too many versions found for '".$app_name."' (Tip: Only need one)");			
								}
								
								if ($total_app_versions == 1)
								{
									// Lets verify it has not been installed
									if (!preg_match("/tar\.gz/", $app_versions[0]))
									{
										$this->print_line("[ERROR] App file name must be tar.gz for '".$app_name."'");			
									}
									else
									{
										//Verifiy if it installed
										if ($this->app_installed($app_name, $user_input))
										{
											$this->print_line("[ERROR] App already installed '".$app_name."' (TIP: make sure '".$user_input."' is empty)");			
										}
										else
										{
											// If index.html exists lets remove this
										
											// Install app.											
											//tar xzf archive.tar.gz -C /destination
											
											$this->print_line("[INFO] Installing '".$app_name."'...");	
															
											if (!$this->is_project_empty($user_input))	// If index.html exists only lets remove this otherwise it is not empty
											{
												$this->print_line("[ERROR] You need to make sure you have removed index.html in '".$user_input."'");			
											}
											else
											{
												// Extract contents
											    $out_lines = array();
											    exec("tar xzf ".Config::$app_folder."/".$app_versions[0]." --strip-components 1 -C ".Config::$site_dir.$user_input."/public/", $out_lines);												
											    $this->print_output($out_lines);
											    											    
											    // Change permission
											    $out_lines = array();
											    exec("chown -R ".Config::$shell_user. " ".Config::$site_dir.$user_input."/public/", $out_lines);											   
											    $this->print_output($out_lines);
											    
											    $out_lines = array();
											    exec("chgrp -R ".Config::$shell_group. " ".Config::$site_dir.$user_input."/public/", $out_lines);
												$this->print_output($out_lines);

											}
											
											// Based on app details, configure additional settings

//ENHANCEMENT: Auto set up config.inc.php with 'blogfish_secret'											
											switch ($app_name)
											{
												/*
												case "phpMyAdmin":
													// Set 'blowfish_secret'
													$random_string = $this->random_string(32);
													$tmp_file = Config::$site_dir.$user_input."/public/config.sample.inc.php";
													$new_file = Config::$site_dir.$user_input."/public/config.inc.php";
													
													if (!file_exists($new_file) && 
														file_exists($tmp_file))
													{
														exec("cp ".$tmp_file. " ".$new_file, $out_lines);
														
														// Edit new config file and update blowfish encryption key
														
														// Set permissions
														exec("chown ".Config::$shell_user. " ".$new_file, $out_lines);
														exec("chgrp ".Config::$shell_group. " ".$new_file, $out_lines);
													}
													break;
												*/
												case "websvn":
														$tmp_file = Config::$site_dir.$user_input."/public/include/distconfig.php";
														$new_file = Config::$site_dir.$user_input."/public/include/config.php";
																										
													// Copy include/distconfig.php to include/config.php
													if (!file_exists($new_file) && 
														 file_exists($tmp_file) &&
														 file_exists(Config::$shell_folder.'/svn/'))
													{
														

														exec("cp ".$tmp_file ." " . $new_file, $out_lines);
													
														if (is_writable($new_file)) {
															$fp=fopen($new_file,"a");
														    fwrite($fp,'$config->parentPath("'.Config::$shell_folder.'/svn/");');														
															$this->print_line("[INFO] App '".$app_name."' : Created config file and set parentPath to '".Config::$shell_folder."/svn/'");			
														    
														    fclose($fp);
														}

    														
														exec("chown ".Config::$shell_user. " ".$new_file, $out_lines);
														exec("chgrp ".Config::$shell_group. " ".$new_file, $out_lines);

													}
													else
													{
														$this->print_line("[WARNING] App '".$app_name."' : Make sure distconfig.php exists and config.php file does not exists within '".Config::$shell_folder."/svn/'");
													}
													break;													
											}
										
											$this->print_line("[INFO] App '".$app_name."' installed in '".$user_input."'");			

										}
									}
								}
							}
							// Make sure the app has not already been installed
							
							//!TODO

							
							//$this->print_line("[INFO] creating app database '".$user_input."' ...");										
							//$this->database_create();
							
						}
						
					}
				}
			}
		}
	}
	
	public function app_remove()
	{
//!TODO
	}
			
	public function backup_list() 
	{		 
	    exec("ls -l ".Config::$backup_folder."/", $out_lines);	    
		$this->print_output($out_lines);	 	
	}
			
	public function backup_svn() 
	{		 
		
		$user_input = $this->project_prompt("Which site to backup repo?");

		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					if (!file_exists(Config::$shell_folder."/svn/".$user_input)) 
					{
						$this->print_line("[ERROR] site project repo does not exist.");	
					}
					else
					{
						////svnadmin dump -q /path/to/repo | bzip2 -9 > filename.bz2
						$this->print_line("Creating svn backup...");	
					    exec(Config::$svn_bin_path."svnadmin dump -q ".Config::$shell_folder."/svn/".$user_input." | gzip -9 > ".Config::$backup_folder."/bk_svn_".$user_input.".gz", $out_lines);	   
						$this->print_output($out_lines);	 
						
						$this->print_line("Site repo backup created. Check ".Config::$backup_folder."/");

					}
				}
			}
		}
	}	

	public function backup_database() 
	{		 
		$user_input = $this->project_prompt("Which site to backup database?");

		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("information_schema","mysql","performance_schema","test"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default database system requirement.");
				} 
				else 
				{
						$this->print_line("Creating database backup...");	

						if (Config::$db_admin_pass) 
						{
							$pass = Config::$db_admin_pass;
						}
						else
						{
							$pass = "";
						}						
						
						// Dump database
					    exec(Config::$mysql_bin_path."mysqldump -u root -p".$pass." ".$user_input." > ".Config::$backup_folder."/bk_database_".$user_input.".sql", $out_lines);	   
						$this->print_output($out_lines);	 

						// Compress backup
					    exec("gzip -9f ".Config::$backup_folder."/bk_database_".$user_input.".sql", $out_lines);	   
						$this->print_output($out_lines);	 
						
						$this->print_line("Site database backup created. Check ".Config::$backup_folder."/");

				
				}
			}
		}
	}
		
	public function restore_svn() 
	{		 
		$user_input = $this->project_prompt("Which site to restore repo?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
				if (in_array($user_input, array("localhost","default","default-ssl"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default web server virtualhost.");
				} 
				else 
				{
					$this->print_line("Making site repo...");					
					if (file_exists(Config::$shell_folder."/svn/".$user_input))
					{
						$this->print_line("[ERROR] Site repo already exists.");
					} 
					else
					{
					    exec("mkdir ".Config::$shell_folder."/svn/".$user_input, $out_lines);	   
						$this->print_output($out_lines);
						
						//Create repo
					    exec(Config::$svn_bin_path."svnadmin create ".Config::$shell_folder."/svn/".$user_input, $out_lines);	   
						$this->print_output($out_lines);	 											     
	
						exec("gunzip -c ".Config::$backup_folder."/bk_svn_".$user_input.".gz | ".Config::$svn_bin_path."svnadmin load ".Config::$shell_folder."/svn/".$user_input, $out_lines);
						$this->print_output($out_lines);	 											     
	
						$this->svn_security($user_input);
	
						$this->print_line("Site repo restored.");
					}
				}
			}
		}
	}
	
	public function restore_database() 
	{		 
		$user_input = $this->project_prompt("Which site to restore database?");
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{
			
				if (in_array($user_input, array("information_schema","mysql","performance_schema","test"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default database system requirement.");
				} 
				else 
				{
					
					// Uncompress backup
					if (!file_exists(Config::$backup_folder."/bk_database_".$user_input.".sql.gz")) 
					{
						$this->print_line("[ERROR] backup ".Config::$backup_folder."/bk_database_".$user_input.".sql.gz does not exist.");	
					}
					else
					{
							
					    exec("gzip -d ".Config::$backup_folder."/bk_database_".$user_input.".sql.gz", $out_lines);	    
						$this->print_output($out_lines);						
							
					}
					
					$this->print_line("Looking for .sql file...");

					if (!file_exists(Config::$backup_folder."/bk_database_".$user_input.".sql")) 
					{
						$this->print_line("[ERROR] backup ".Config::$backup_folder."/bk_database_".$user_input.".sql.gz does not exist.");	
					}
					else
					{

						if (Config::$db_admin_pass) 
						{
							$pass = Config::$db_admin_pass;
						}
						else
						{
							$pass = "";
						}						
											
					
					    exec("cat ".Config::$backup_folder."/bk_database_".$user_input.".sql | ".Config::$mysql_bin_path."mysql -u root -p".$pass." ".$user_input, $out_lines);	    
						$this->print_output($out_lines);						

						$this->print_line("site database restored.");											
					}
					
				}
			}
		}
	}	
	
	public function database_exists($db_name)
	{
//!TODO
	}
	
	public function database_create()
	{
		$user_input = $this->project_prompt("Which site to create database?");
	
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{

				if (in_array($user_input, array("information_schema","mysql","performance_schema","test"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default database system requirement.");
				} 
				else 
				{	
				    exec(Config::$mysql_bin_path."mysqladmin -u root -p".Config::$db_admin_pass." create ".$user_input, $out_lines);	    
					$this->print_output($out_lines);						
				}
			}
		}		
	}

	public function database_delete()
	{
		$user_input = $this->project_prompt("Which site to create database?");
	
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{

				if (in_array($user_input, array("information_schema","mysql","performance_schema","test"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default database system requirement.");
				} 
				else 
				{	
				    exec(Config::$mysql_bin_path."mysqladmin -u root -p".Config::$db_admin_pass." drop ".$user_input." -f", $out_lines);	    
					$this->print_output($out_lines);						
				}
			}
		}		
	}
	
	public function _database_exists($db_name)
	{
	
		if (Config::$database_on)
		{
			$this->print_line("[INFO] Checking if database exists...");
			
		    exec("echo \"SHOW DATABASES LIKE '".$db_name."';\" | ".Config::$mysql_bin_path."mysql -u root -p".Config::$db_admin_pass, $out_lines);	 
	
			if (is_array($out_lines) && count($out_lines) == 2)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return TRUE;	// We bypass the database check
		}
	}

	public function database_list()
	{
		if (Config::$db_admin_pass) 
		{
			$pass = Config::$db_admin_pass;
		}
		else
		{
			$pass = "";
		}		
		
	    exec("echo \"SHOW DATABASES;\" | ".Config::$mysql_bin_path."mysql -u root -p".$pass." -t", $out_lines);	    
		$this->print_output($out_lines);						
	}	
	
/*
//!TODO: Need to verify if we really need to allow this feature.
	public function database_security()
	{
		echo "Which site to create database?\n";
		$user_input = trim(fgets(STDIN));	
		
		
		if (empty($user_input)) 
		{
			$this->print_line("[ERROR] site domain name missing.");	
		} 
		else 
		{
			
			$this->print_line("Validating site...");
			
			if (!$this->valid_filename($user_input)) 
			{
				$this->print_line("[ERROR] The file name can only contain \"a-z\", \"0-9\", \".\" and \"-\" and must be lower case");
			}
			else
			{

				if (in_array($user_input, array("information_schema","mysql","performance_schema","test"))) 
				{
					$this->print_line("[ERROR] You cannot use '".$user_input."', this is a default database system requirement.");
				} 
				else 
				{
					if (Config::$db_admin_pass) 
					{
						$pass = Config::$db_admin_pass;
					}
					else
					{
						$pass = "";
					}		

				    exec("echo \"GRANT SELECT , INSERT , UPDATE , DELETE ON  `sandbox.dev` . * TO  'dbuser'@'%';\" | mysql -u root -p".$pass." ".$user_input, $out_lines);	    
					$this->print_output($out_lines);				
				}
			}
		}		
	}
*/	


	public function write_check()
	{
		$errors = array();		

		$files = array();

		// Folders that will be required to be writable		
		$files[] = Config::$shell_folder."/svn/";
		$files[] = Config::$site_dir;
		$files[] = Config::$shell_folder."/vhosts/";
		$files[] = Config::$backup_folder."/";
	
		foreach($files as $file) 
		{
		    if (!is_writable($file)) 
		    {
			    $errors[] = "[WARNING] ".$file." is not writable.";
		    }
	    }
	    
		return $errors;
	}

	public function check_shell_env($debug=FALSE) 
	{
		$errors = array();		
		$files = array();
		
		
		//System environment requirements
		$files[] = "/etc/apache2/";
		
		if (Config::$virtual_machine == TRUE)
		{
//Not allowed in OSx environment - Maybe different
//			$files[] = "/etc/network/interfaces";
//			$files[] = "/etc/network/interfaces.static";
//			$files[] = "/etc/network/interfaces.original";
//			$files[] = "/etc/network/interfaces.dynamic";	
		}
		
		//Folders
		$files[] = Config::$shell_folder."/svn/";
		$files[] = Config::$site_dir;
		$files[] = Config::$shell_folder."/vhosts/";
		$files[] = Config::$backup_folder."/";
		$files[] = Config::$shell_folder."/BURDShell/apps/";
		$files[] = Config::$shell_folder."/BURDShell/templates/";
				
		//BURDShell templates
		$files[] = Config::$shell_folder."/BURDShell/templates/osx-apache2-virtualhost.txt";
		$files[] = Config::$shell_folder."/BURDShell/templates/site-index-file.txt";

		if ($debug)
		{
			echo "Files n folders checked are:";
			print_r($files);
		}
				
		foreach($files as $file) 
		{
		    if (!file_exists($file)) 
		    {
			    $errors[] = $file." does not exist.";
			}
		}
		
		if (count($errors)) 
		{
			$this->errors_found = TRUE;
		}
		return $errors;
	}
}