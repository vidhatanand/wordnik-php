<?php

/**
 * This is the official PHP client library for the Wordnik API.
 *
 * It wraps all the API calls listed here: http://developer.wordnik.com/docs
 * with PHP methods, and returns arrays of standard php objects containing the results.
 *
 * To use the API you'll need a key, which you can apply for here:
 * http://developer.wordnik.com/
 *
 * After you receive your key assign it to the API_KEY constant, below.
 * Then, to get an array of definition objects, do something like this:
 *
 * require_once('Wordnik.php');
 * $definitions = Wordnik::instance()->getDefinitions('donkey');
 *
 * $definitions will hold an array of objects, which can be accessed individually:
 * $definitions[0]->word
 *
 * Or you can loop through the results and display info about each,
 * which could look something like this in a template context:
 *
 * <ul>
 *   <? foreach ($definitions as $definition): ?>
 *     <li>
 *       <strong><?= $definition->word ?></strong: 
 *       <?= $definition->text ?>
 *     </li>
 *   <? endforeach; ?>
 * </ul>
 *
 * Please send comments or questions to apiteam@wordnik.com.
 */
class Wordnik {
   // the base url for all wordnik api calls
   const BASE_URI = 'http://api.wordnik.com/v4';
   private static $instance;
   private $auth_info;
   private $api_key;
   
   private function __construct($api_key) {
      $this->api_key = $api_key;
   }

   /*
    * If there's an existing Wordnik instance, return it, otherwise create and return a new one.
    */


   public static function instance($api_key) {
      if (self::$instance == NULL) {
         if (!isset($api_key) || $api_key == "YOUR_API_KEY" || trim($api_key) == '') {
            throw new Exception("You need to specify a valid api_key!");
         }
         self::$instance = new Wordnik($api_key);
      }

      return self::$instance;
   }


/********************
 * /account functions
 ********************/

   /**
    * Authenticate this instance of Wordnik.
    * You need to do this before making any list-related calls.
    * More info: http://developer.wordnik.com/docs#!/account/authenticate
    * @param Array $params
    *    keys:
    *       username - required
    *       password - required
    * @return String
    */
   public function authenticate(array $params = array()) {
      $this->validateParams($params, array("username", "password"), __FUNCTION__);
      $username = $this->popKey($params, "username");

      $this->auth_info = $this->callApi('/account.json/authenticate/' . urlencode($username), $params);
      
      return $this->auth_info;
   }

   /*
    * Get all of the authenticated user's lists.
    * Note: you must call getAuthToken before calling this.
    * More info: http://docs.wordnik.com/api/methods#lists
    */

   /**
    * More info: http://developer.wordnik.com/docs#!/account/get_word_lists_for_current_user
    * @param array $params
    *    keys:
    *       skip
    *       limit
    * @return array 
    */
   public function getWordLists(array $params = array()) {
      $this->ensureAuthentic();
//      $params['api_key'] = $this->api_key;
//      $params['auth_token'] = $this->auth_info->token;
      
      return $this->callApi('/account.json/wordLists', $params);
   }

   /**
    *
    * @param array $params
    * @return Object 
    */
   public function getApiTokenStatus(array $params = array()) {
      $params['api_key'] = $this->api_key;
      
      return $this->callApi('/account.json/apiTokenStatus', $params);
   }
   
   public function getUser(array $params = array()) {
      $this->ensureAuthentic();
//      $params['api_key'] = $this->api_key;
//      $params['auth_token'] = $this->auth_info->token;
      
      return $this->callApi('/account.json/user', $params);
   }
   
   /*
    * Ensures that this instance of Wordnik has been authenticated.
    * (See getAuthToken)
    */
   private function ensureAuthentic() {
      if (!isset($this->auth_info)) {
         throw new Exception("You need to call getAuthToken before requesting this api resource.");
      }
      
      return true;
   }

/********************
 * /word functions
 ********************/
   
   /**
    * More info: http://docs.wordnik.com/api/methods#examples
    * @param array $params
    *    keys:
    *       word              - required
    *       includeDuplicates
    *       contentProvider
    *       useCanonical
    *       skip
    *       limit
    * @return array containing examples for the word
    */
   public function getExamples(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");

      return $this->callApi('/word.json/' . rawurlencode($word) . '/examples', $params);
   }
   
   /**
    * More info: http://developer.wordnik.com/docs#!/word/get_word
    * @param array $params
    *    keys:
    *       word                 - required
    *       useCanonical
    *       includeSuggestions
    * @return Object 
    */
   public function getWord(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");
      
      return $this->callApi('/word.json/' . rawurlencode($word), $params);
   }

   /**
    * More info: http://developer.wordnik.com/docs#!/word/get_definitions
    * @param array $params
    *    keys:
    *       word              - required
    *       limit
    *       partOfSpeech
    *       includeRelated
    *       sourceDictionaries
    *       useCanonical
    *       includeTags
    * @return srray containings definitions of the word.
    */
   public function getDefinitions(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");
      
      return $this->callApi('/word.json/' . rawurlencode($word) . '/definitions', $params);
   }
   
   /**
    * More info: http://developer.wordnik.com/docs#!/word/get_top_example
    * @param array $params
    *    keys:
    *       word              - required
    *       contentProvider
    *       useCanonical
    * @return Object 
    */
   public function getTopExample(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");
      
      return $this->callApi('/word.json/' . rawurlencode($word) . '/topExammple', $params);
   }

   /**
    * More info: http://developer.wordnik.com/docs#!/word/get_text_pronunciations
    * @param Array $params
    *    keys:
    *       word              - required
    *       useCanonical
    *       sourceDictionary
    *       typeFormat
    *       limit
    * @return Array
    */
   public function getTextPronunciations(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");
      
      return $this->callApi('/word.json/' . rawurlencode($word) . '/pronunciations', $params);
   }
   
   /**
    * More info: http://developer.wordnik.com/docs#!/word/get_hyphenation
    * @param array $params
    *    keys:
    *       word              - required
    *       useCanonical
    *       sourceDictionary
    *       limit
    * @return array 
    */
   public function getHyphenation(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");
      
      return $this->callApi('/word.json/' . rawurlencode($word) . '/hyphenation', $params);
   }

   /**
    * More info: http://developer.wordnik.com/docs#!/word/get_word_frequency
    * @param array $params
    *    keys:
    *       word           - required
    *       useCanonical
    *       startYear
    *       endYear
    * @return array
    */
   public function getFrequency(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");
      
      return $this->callApi('/word.json/' . rawurlencode($word) . '/frequency', $params);
   }

   /**
    * More info: http://developer.wordnik.com/docs#!/word/get_phrases
    * @param array $params
    *    keys:
    *       word           - required
    *       limit
    *       wlmi
    *       useCanonical
    * @return array 
    */
   public function getPhrases(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");
      
      return $this->callApi('/word.json/' . rawurlencode($word) . '/phrases', $params);
   }

   /**
    * More info: http://developer.wordnik.com/docs#!/word/get_related
    * @param Array $params
    *    keys:
    *       word              - required
    *       partOfSpeech
    *       sourceDictionary
    *       limit
    *       useCanonical
    *       type
    * @return Array 
    */
   public function getRelatedWords(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");

      return $this->callApi('/word.json/' . rawurlencode($word) . '/related', $params);
   }
   
   /**
    * More info: http://developer.wordnik.com/docs#!/word/get_audio
    * @param array $params
    *    keys:
    *       word           - required
    *       useCanonical
    *       limit
    * @return array 
    */
   public function getAudio(array $params = array()) {
      $this->validateParams($params, array("word"), __FUNCTION__);
      $word = $this->popKey($params, "word");
      
      return $this->callApi('/word.json/' . rawurlencode($word) . '/audio', $params);
   }

/********************
 * /words functions
 ********************/

   /**
    * Chose not to create getWordsSearch. Functionality is identical to getWordsSearchQuery.
    * getWordsSearchQuery implements the newer API function.
    * More info: http://developer.wordnik.com/docs#!/words/search_words
    */

   /**
    * More info: http://developer.wordnik.com/docs#!/words/get_word_of_the_day
    * @param Array $params
    *    keys:
    *       date     (format: YYYY-MM-dd)
    *       category
    *       creator
    * @return Array
    */
   public function getWordOfTheDay(array $params = array()) {
      
      return $this->callApi('/words.json/wordOfTheDay', $params);
   }

   /**
    * More info: http://developer.wordnik.com/docs#!/words/search_words_new
    * @param array $params
    *    keys:
    *       query                - required
    *       caseSensitive        - default: "true"
    *       includePartOfSpeech
    *       excludePartOfSpeech
    *       minCorpusCount
    *       maxCorpusCount
    *       minDictionaryCount
    *       maxDictionaryCount
    *       minLength
    *       maxLength
    *       skip                 - default: 0
    *       limit                - default: 10
    * @return array containing word objects
    */
   public function getWordsSearchQuery(array $params = array("caseSensitive"=>"true", "skip"=>0, "limit"=>10)) {
      $this->validateParams($params, array("query"), __FUNCTION__);
      $query = $this->popKey($params, "query");
      if (!isset($params['caseSensitive']) || trim($params['caseSensitive']) == '') {
         $params['caseSensitive'] = "true";
      }
      if (!isset($params['skip'])) {
         $params['skip'] = 0;
      }
      if (!isset($params['limit'])) {
         $params['limit'] = 10;
      }

      return $this->callApi('/words.json/search/' . rawurlencode($query), $params);
   }
   
   /**
    * More info: http://developer.wordnik.com/docs#!/words/get_random_words
    * @param array $params
    *    keys:
    *       hasDictionaryDef
    *       includePartOfSpeech
    *       excludePartOfSpeech
    *       minCorpusCount
    *       maxCorpusCount
    *       minDictionaryCount
    *       maxDictionaryCount
    *       minLength
    *       maxLength
    *       sortBy
    *       sortOrder
    *       limit
    * @return array 
    */
   public function getRandomWords(array $params = array()) {
      return $this->callApi('/words.json/randomWords', $params);
   }
   
   /**
    * More info: http://developer.wordnik.com/docs#!/words/get_random_word
    * @param array $params
    *    keys:
    *       hasDictionaryDef     - default: "true"
    *       includePartOfSpeech
    *       excludePartOfSpeech
    *       minCorpusCount
    *       maxCorpusCount
    *       minDictionaryCount
    *       maxDictionaryCount
    *       minLength
    *       maxLength
    * @return array
    */
   public function getRandomWord(array $params = array("hasDictionaryDef"=>"true")) {
      if (!isset($params['hasDictionaryDef'])) {
         $params['hasDictionaryDef'] = "true";
      }
      return $this->callApi('/words.json/randomWord', $params);
   }

/********************
 * /wordList functions
 ********************/
   
   /**
    * Fetches a wordlist by ID (permalink)
    * More info: http://developer.wordnik.com/docs#!/wordList/get_word_list_by_id
    * @param array $params
    *    keys:
    *       wordListId - required (permalink)
    * @return object 
    */
   public function getWordList(array $params = array()) {
      $this->validateParams($params, array("wordListId"), __FUNCTION__);
      $this->ensureAuthentic();
//      $params['auth_token'] = $this->auth_info->token;
      $wordListId = $this->popKey($params, "wordListId");
      
      return $this->callApi('/wordList.json/' . $wordListId, $params);
   }
   
   /**
    * Fetches words in a wordlist
    * More info: http://developer.wordnik.com/docs#!/wordList/get_word_list_words
    * @param array $params
    *    keys:
    *       wordListId  - required (permalink)
    *       sortBy      - default: "createDate"
    *       sortOrder   - default: "desc"
    *       skip
    *       limit
    * @return type 
    */
   public function getWordListWords(array $params = array("sortBy"=>"createDate", "sortOrder"=>"desc")) {
      $this->validateParams($params, array("wordListId"), __FUNCTION__);
      if (!isset($params['sortBy']) || trim($params['sortBy']) == '') {
         $params['sortBy'] = 'createDate';
      }
      if (!isset($params['sortOrder']) || trim($params['sortOrder']) == '') {
         $params['sortOrder'] = 'desc';
      }
      $this->ensureAuthentic();
//      $params['auth_token'] = $this->auth_info->token;
      $wordListId = $this->popKey($params, "wordListId");
      
      return $this->callApi('/wordList.json/' . $wordListId . '/words', $params);
   }
   
   /**
    * More info: http://developer.wordnik.com/docs#!/wordList/add_words_to_word_list
    * @param array $params
    *    keys:
    *       wordListId  - required (permalink)
    *       words       - required (array of words)
    * @return type 
    */
   public function addWordsToList(array $params = array()) {
      $this->validateParams($params, array("wordListId", "words"), __FUNCTION__);
      $this->ensureAuthentic();
      $wordListId = $this->popKey($params, "wordListId");
//      $params['auth_token'] = $this->auth_info->token;
      $words = $this->popKey($params, "words");
      $request_body = $this->makeRequestBody($words);

      return $this->callApi('/wordList.json/' . $wordListId . '/words', $params, 'post', $request_body);
   }

   /**
    * More info: http://developer.wordnik.com/docs#!/wordList/delete_word_list
    * @param array $params
    *    keys:
    *       wordListId  - required (permalink)
    * @return none 
    */
   public function deleteList(array $params = array()) {
      $this->validateParams($params, array("wordListId"), __FUNCTION__);
      $this->ensureAuthentic();
//      $params["auth_token"] = $this->auth_info->token;
      $wordListId = $this->popKey($params, "wordListId");
      
      return $this->callApi('/wordList.json/' . $wordListId, $params, 'delete');
   }

   /*
    * Delete a word from the given list
    * Note: you must call getAuthToken before calling this.
    * Required params:
    *   wordstring : the word to delete from the list
    *   list_permalink : the list's permalink id
    */

   /**
    *
    * @param array $params
    *    keys:
    *       wordListId  - required (permalink)
    * @return none 
    */
   public function updateList(array $params = array()) {
      $this->validateParams($params, array("wordListId", "words"), __FUNCTION__);
      $this->ensureAuthentic();
      $wordListId = $this->popKey($params, "wordListId");
//      $params['auth_token'] = $this->auth_info->token;
      $words = $this->popKey($params, "words");
      $request_body = $this->makeRequestBody($words);

      return $this->callApi('/wordList.json/' . $wordListId, $params, 'put', $request_body);
   }
   
   private function makeRequestBody(array $words = array()) {
      $request_body = array();
      foreach ($words as $word) {
         $obj = new stdClass;
         $obj->word = $word;
         $request_body[] = $obj;
      }
      
      return $request_body;
   }

   private function popKey(array &$params, $key) {
      $value = $params[$key];
      unset($params[$key]);
      
      return $value;
   }
   
   private function validateParams(array $params, array $required, $func_name) {
      foreach($required as $key) {
         if (!isset($params[$key]) || (!is_array($params[$key]) && trim($params[$key]) == '')) {
            throw new InvalidArgumentException("$func_name expects $key to be a string");
         }
      }
   }
   
   /*
    * Utility method to call json apis.
    * This presumes you want JSON back; could be adapted for XML pretty easily.
    */

   private function callApi($url, array $params=array(), $method='get', array $request_body=array()) {
      $data = null;

      $headers = array();
      $headers[] = "Content-type: application/json";
      $headers[] = "api_key: " . $this->api_key;
      if (isset($this->auth_info->token)) {
         $headers[] = "auth_token: " . $this->auth_info->token;
      }

      $url = (self::BASE_URI . $url);

      $curl = curl_init();
      $timeout = 10;
      curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // return the result on success, rather than just TRUE
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

      if (!empty($params)) {
         $url = ($url . '?' . http_build_query($params));
      }
      
      $method = strtoupper($method);
      $encoded_body = json_encode($request_body);
      if ($method == 'POST') {
         curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded_body);
         curl_setopt($curl, CURLOPT_POST, true);
      }
      if (!in_array($method, array('POST','GET'))) {
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
      }
      if ($method == 'PUT') {
         $fp = fopen('php://temp/maxmemory:256000', 'w');
         if(!fp) {
            throw new Exception("Could not create file for PUT request");
         }
         fwrite($fp,$encoded_body);
         fseek($fp, 0);
         
         $options = array(
             CURLOPT_BINARYTRANSFER => true,
             CURLOPT_INFILE => $fp,
             CURLOPT_INFILESIZE => strlen($encoded_body)
         );
         curl_setopt_array($curl, $options);
      }

      curl_setopt($curl, CURLOPT_URL, $url);

//      curl_setopt($curl, CURLINFO_HEADER_OUT, true);

      // make the request
      $response = curl_exec($curl);
      $response_info = curl_getinfo($curl);

      // handle the response based on the http code
      if ($response_info['http_code'] == 0) {
         throw new Exception("TIMEOUT: api call to " . $url . " took more than {$timeout}s to return");
      } else if ($response_info['http_code'] == 200) {
         $data = json_decode($response);
      } else if ($response_info['http_code'] == 401) {
         throw new Exception("Unauthorized API request to " . $url . ": " . json_decode($response)->message);
      } else if ($response_info['http_code'] == 404) {
         $data = null;
      } else {
         throw new Exception("Can't connect to the api: " . $url . " response code: " . $response_info['http_code']);
      }

      return $data;
   }

}
