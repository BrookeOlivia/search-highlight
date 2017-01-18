<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
  * ExpressionEngine - by EllisLab
  *
  * @package     ExpressionEngine
  * @author      ExpressionEngine Dev Team
  * @copyright   Copyright (c) 2003 - 2011, EllisLab, Inc.
  * @license     http://expressionengine.com/user_guide/license.html
  * @link        http://expressionengine.com
  * @since       Version 2.0
  */

/*****************************************************************************/

/**
  * Search Highlight Plugin
  *
  * @package     ExpressionEngine
  * @subpackage  Addons
  * @category    Plugin
  * @author      Brooke Bailey   
  */

$plugin_info = array(
    'pi_name'         => 'Search Highlight ',
    'pi_version'      => '1.0',
    'pi_author'       => 'Brooke Bailey',
    'pi_author_url'   => 'http://example.com/',
    'pi_description'  => 'Returns search keywords highlighted for ease of the 
                          user to find exactly what they are looking for.',
    'pi_usage'        => Search_highlight::usage()
);

/*****************************************************************************/

class Search_highlight 
{
    public $return_data;

	/* Connstructor
        @param  no parameters.
        @return returns the completed highlighted excerpt according to search 
        results.
     */
    public function __construct()
    {
        /* this object gives our plugin access to the built-in methods, that 
         * access the database.  
         */   
            $this->EE =& get_instance();

        /* In order to have the appropriate information for our keywords we
         * need to grab part of EE's search function from mod.search.php (
         * system/expressionengine/modules/search/mod.search.php).  We are 
         * going to store the search id that is gathered from the search form
         * and then we are going to pass it to the sql query.  If the sql query
         * finds a result then we are going to store it in our keyword variable 
         * (same one that is used in the function).  
         */

            //Tell our query where to look for our query string.
            // attribute in 
            $search_id = $this->EE->TMPL->fetch_param('query');

        /** ----------------------------------------
        /**  Fetch the cached search query       
        /** ----------------------------------------*/
            $query = $this->EE->db->query("SELECT keywords FROM exp_search WHERE search_id = '"
                    .$this->EE->db->escape_str($search_id)."'");

            if ($query->num_rows() == 1)
            {
                $keyword  = ($query->row('keywords'));
            }

        //Store our excerpt values into $excerpt
            $excerpt =  $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata,'excerpt');

        /* Remove all of the slashes that ExpressionEngine puts in and then 
         * Split the {excerpt} into words so that we can see if any of
         * them match what the user has put into the search.  Also split out 
         * $keyword so that we can match it on each word that was entered.
         */

        //strip slashes
            $excerpt = stripslashes($excerpt);             

        //strip punctuation    
            $excerpt = preg_replace('/[^a-z]+/i', ' ', $excerpt); 

        //initiate excerpt variable
            $excerpt = preg_split("/[\s,]+/", $excerpt);       

        //initiate a keyword Split
            $keyword_split = preg_split("/[\s,]+/", $keyword);   

        //initiate a $result count to use in for loop
            $result = count($keyword_split);     
        
        /* If the result is greater than 1 (the user entered something into the
         * search box) then loop through all of the words and the excerpt and 
         * find the ones that match.  When you find the ones that match add the 
         * strong tag and then add it to the $searchWords array and then exit   
         * the loop ($i = $result). Once the for loop has been exited (either 
         * by the program or not matched) check to see if a keyword was found 
         * if it wasn't then add the word to the array.  After all words have  
         * been looped through output the array to the user with the user.
        */
            if ($result >= 1) 
            {   
                $sentance_words = array(); //Blank array to store the sentence 
                foreach($excerpt as $word)
                {   
                    //Declared and initiated variable to store the discovered 
                    //keyword
                        $found_keyword ="";     
                    
                    for ($i=0; $i < $result; $i++) 
                    {   
                        if (strtolower($word) == strtolower($keyword_split[$i]))
                        {  
                            //Place strong tags around the word and set to 
                            //variable found
                                $found = "<strong><em> $word </em></strong>";

                            //add the edited keyword to the loop
                                array_push($sentance_words, "$found");  

                            //Initiate $foundKeyWord to yes to avoid 
                            //duplicated words in $search_words.
                                $found_keyword = "yes"; 
                            
                            //exit the loop and go to the next word of excerpt
                                $i = $result;   

                        } //end if statements
                    } //end for loop

                    //If the word is not in the array already then add
                    //it to the array.
                        if ($found_keyword != "yes") 
                        {   
                            //add the word to our array
                                array_push($sentance_words, $word); 
                        }

                } //end foreach 
                    $final_search_excerpt = implode(" ", $sentance_words);
            } //end if statement
        // Returns our excerpt
            $this->return_data = $final_search_excerpt ."<br>";
    }	
    
    /**************************************************************************
     * Plugin Usage
     **************************************************************************
     * This function tells about how the plugin should be used.  It also makes
     * sure that we are using output buffering.
     */

    public static function usage()
    {
        ob_start();
?>
 ---------------------------
     PARAMETERS
     query - where is the query search result string in your url. 
        - (defaulted to {segment_3})
 ---------------------------
 This plugin has no parameters

 ---------------------------
     EXAMPLE
 ---------------------------
     {exp:search_highlight query="{segment_3}"}
         {excerpt}
     {/exp:search_highlight}

 User input in EE search: David Tennant
 - Returns:  Companion Tom Baker and <strong> David </strong> 
                <strong> Tennant </strong> Classic Doctor Who 
<?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}

/* End of file pi.search_highlight.php */
/* Location: ./system/expressionengine/third_party/search_highlight/pi.search_highlight.php */