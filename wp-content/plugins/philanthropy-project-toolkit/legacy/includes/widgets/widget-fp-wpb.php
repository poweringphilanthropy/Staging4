<?php

// Creating the widget 
class wpb_fp_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			// Base ID of your widget
			'wpb_fp_widget', 

			// Widget name will appear in UI
			__('Follow Up question', 'wpb_widget_domain'), 

			// Widget description
			array( 'description' => __( 'to view follow up questions', 'wpb_widget_domain' ), ) 
		);
	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
	    $url = site_url().$_SERVER['REQUEST_URI'];
	    $campaign = charitable_get_current_campaign();
	    $subs = Ninja_Forms()->subs()->get( array('form_id'   => 11, 'user_id'   => get_the_author_meta( 'ID' )) );
		
		if($subs==NULL){
		    return false;
		} else {
			$title = apply_filters( 'widget_title', $instance['title'] );
			// before and after widget arguments are defined by themes

			echo $args['before_widget'];
			if ( ! empty( $title ) )
			echo "<div class='title-wrapper'><h3 class='widget-title'>" . $title . "</h3></div>";
			        
			    echo '<div class="title-wrapper"><h2 class="block-title">Campaign Reflections</h2></div>';

			    $num = array(
			    17 => 'First Name',
			    18 => 'Last Name',
			    19 => 'Email Address',
			    20 => 'Campaign URL',
			    24 => 'What was a highlight of this experience for you?',
			    25 => 'What was a significant moment for you during this experience, and what about that moment matters?',
			    26 => 'What will you remember about this experience 5 years from now?',
			    27 => 'What did you learn from this experience?',
			    28 => 'What did you accomplish?',
			    29 => 'What\'s next for you in the world of philanthropy?',
			    58 => 'Did you incur any campaign-related costs for which you require reimbursement? If so, please describe each expense in detail:',
			    59 => 'If you raised money for your own cause (and not a registered 501(c)3 organization during your campaign), please describe in detail proof that the money raised during your campaign (or the goods or services purchased with this money) was actually delivered to the designated beneficiary upon completion of your campaign:',
			    32 => 'Rating',
			    30 => 'Please tell us about your experience running a campaign on Greeks4Good: Which features or aspects of the platform were most helpful? Is there anything we can improve?',
			    33 => ' Is there anything else you\'d like to share with us about this experience?',
			    61 => 'documentation (receipts, emails, etc) for each campaign-related expense for which you require reimbursement:',
			    60 => 'Please upload any pictures or videos that illustrate your experience with running this campaign:',
			    72  => 'What surprised you during your campaign?',
			    73  => 'Upload a photo that expresses something meaningful about this experience (caption optional):',
			    75  =>  'What would be your theme song from this experience? If you write an original song, provide a link to a recording or video!',
			    76  => 'What question do you wish someone would ask you about this experience?'
			    );
			    
			global $wpdb;
			$table_name = $wpdb->prefix . "options";
			$retrieve_data = $wpdb->get_results( "SELECT option_value FROM $table_name where option_name='siteurl'" );
			$site_url =  $retrieve_data[0]->option_value;

			$subs = Ninja_Forms()->subs()->get( array('form_id'   => 11, 'user_id'   => get_the_author_meta( 'ID' )) );
			foreach($subs as $i => $key){
			    $url = site_url().$_SERVER['REQUEST_URI'];
			    $url2 = $key->fields[20];

			    if (strpos($url2,'staging3.') !== false) {
			    $url2 = str_replace('staging3.','',$url2);
			}
			    
			    
			    
			    if ($url == $url2 )
			    {
			    if(count($key->fields[60])!=''){        
			        echo '<p class="qus">'.$num[60].'</p>';
			        
			        
			       foreach($key->fields[60] as $media){
					$filename = $media['user_file_name'];   
				    $image_extensions_allowed = array('jpg', 'jpeg', 'png', 'gif','bmp');
			        $video_extensions_allowed = array("webm", "mp4", "ogv" , "3gp");
			        $file_parts = pathinfo($filename);
			            if(in_array($file_parts['extension'],$image_extensions_allowed)){
			                echo '<a href="'.$media['file_url'].'"><img src="'.site_url()."/wp-content/plugins/philanthropy-project-toolkit/assets/timthumb.php?src=".$media['file_url'].'&h=100&w=100"></a>';
			                }
			            if(in_array($file_parts['extension'],$video_extensions_allowed)){
			                echo '<video width="400" controls>
			  <source src="'.$media['file_url'].'" type="'.$file_parts['extension'].'">
			  Your browser does not support HTML5 video.
			</video>
			';
			                }               
			        echo '<br><br>';

					   }
			    }


			    if($key->fields[24]!=''){       
			        echo '<p class="qus">'.$num[24].'</p>';
			        echo '<p class="ans">'.$key->fields[24].'</p><br>';
			    }
			        
			    if($key->fields[25]!=''){       
			        echo '<p class="qus">'.$num[25].'</p>';
			        echo '<p class="ans">'.$key->fields[25].'</p><br>';}

			    if($key->fields[72]!=''){       
			        echo '<p class="qus">'.$num[72].'</p>';
			        echo '<p class="ans">'.$key->fields[72].'</p><br>';}

			    if($key->fields[73]!=''){       
			        echo '<p class="qus">'.$num[73].'</p>';
			        foreach($key->fields[73] as $media ){

			        $filename = $media['user_file_name'];
			        $image_extensions_allowed = array('jpg', 'jpeg', 'png', 'gif','bmp');
			        $file_parts = pathinfo($filename);
			            if(in_array($file_parts['extension'],$image_extensions_allowed)){
			                echo '<a href="'.$media['file_url'].'"><img src="'.site_url()."/wp-content/plugins/philanthropy-project-toolkit/assets/timthumb.php?src=".$media['file_url'].'&h=100&w=100"></a>';
			                }
			            
			        echo '<br><br>';			
						}

			    }
			        
			    if($key->fields[75]!=''){       
			        echo '<p class="qus">'.$num[75].'</p>';
			        echo '<p class="ans">'.$key->fields[75].'</p><br>';}
			        
			    if($key->fields[26]!=''){       
			        echo '<p class="qus">'.$num[26].'</p>';
			        echo '<p class="ans">'.$key->fields[26].'</p><br>';}
			        
			    if($key->fields[27]!=''){       
			        echo '<p class="qus">'.$num[27].'</p>';
			        echo '<p class="ans">'.$key->fields[27].'</p><br>';}
			        
			    if($key->fields[28]!=''){       
			        echo '<p class="qus">'.$num[28].'</p>';
			        echo '<p class="ans">'.$key->fields[28].'</p><br>';}
			        
			    if($key->fields[76]!=''){       
			        echo '<p class="qus">'.$num[76].'</p>';
			        echo '<p class="ans">'.$key->fields[76].'</p><br>';}
			        
			    if($key->fields[29]!=''){       
			        echo '<p class="qus">'.$num[29].'</p>';
			        echo '<p class="ans">'.$key->fields[29].'</p><br>';}
			    }
			    
			echo '<div></div>';
			echo $args['after_widget'];
			}
		}
	}

	    
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} // Class wpb_widget ends here