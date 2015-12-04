<?php
/* Author: Mahdi Hazaveh <mahdi@hazaveh.net> */
require_once 'medoo.min.php';

class convertAgent {

// Initializing default variables.

  public $idCol = 1;
  public $indexName;
  public $tableName;
  public $database;
  public $elasticSearch;
  public $colors;
  public $resume;


  public function __construct() {

      $this->colors = new Colors();
      try {
      $this->database = new medoo([
          'database_type' => 'mysql',
          'database_name' => DB_NAME,
          'server' => DB_HOST,
          'username' => DB_USER,
          'password' => DB_PASS,
          'charset' => 'utf8',
          'port' => DB_PORT,
      ]);
      } catch (Exception $e) {
          echo PHP_EOL . $this->colors->
          getColoredString("Error While Connection to mySQL Database,\nPlease check your Database connection settings in config.php" ,
          'red') . PHP_EOL;
          die();
      }
      if (USE_DB_COLUMN == TRUE) {
          $this->idCol = DB_COL;
      }
      global $ES_HOST;
      $this->elasticSearch = \Elasticsearch\ClientBuilder::create()->setHosts($ES_HOST)->build();
  }


  // This function understands the Table Status and number of rows.
  public function understand() {
      echo PHP_EOL . $this->colors->
          getColoredString('mySQLtoes 1.0', 'green')
          . PHP_EOL;
      sleep(1);
      echo $this->colors->
          getColoredString('I\'m looking for the number of rows in the MySQL Table.', "green" )
          . PHP_EOL;
      sleep(1);
      $totalRrows = $this->database->count(DB_TABLE);
      echo PHP_EOL .$this->colors->
          getColoredString('I found ' . $totalRrows . ' items in ' . DB_TABLE . ' table!', 'yellow')
          . PHP_EOL;
      sleep(1);
      echo PHP_EOL .$this->colors->
          getColoredString('You set a limit of ' . Q_LIMIT . ' per select query in your config file.', 'black' , 'yellow')
          . PHP_EOL . PHP_EOL;
      echo $this->colors->getColoredString('Calculating number of queries to be executed.' , 'green');
      sleep(1); echo $this->colors->getColoredString('.', 'green');
      sleep(1); echo $this->colors->getColoredString('.', 'green') . PHP_EOL. PHP_EOL;
      $this->Querying($totalRrows);

  }

  public function execute($x) {
          $startTime = microtime(true);
          $jpnResult = $this->database->query($x)->fetchAll(PDO::FETCH_ASSOC);


          echo  PHP_EOL . 'Successful query.' . PHP_EOL;

          $params = ['body' => []];
          echo $this->colors->
              getColoredString('Getting ' . Q_LIMIT . ' records ready to insert.', 'yellow') . PHP_EOL;


      if ($this->idCol == DB_COL) {
          foreach ($jpnResult as $column => $content) {

              $params['body'][] = [
                  'index' => [
                      '_index' => ES_INDEX,
                      '_type'  => ES_TYPE,
                      '_id'    => $content[$this->idCol],
                  ]
              ];
              $params['body'][] = [$column => $content];
          }
      } else {
          foreach ($jpnResult as $column => $content) {

              $params['body'][] = [
                  'index' => [
                      '_index' => ES_INDEX,
                      '_type'  => ES_TYPE,
                      '_id'    => $this->idCol,
                  ]
              ];
              $params['body'][] = [$column => $content];
              $this->idCol++;
          }
      }



            try {
          $responses = $this->elasticSearch->bulk($params);
            } catch (Exception $e) {
                echo PHP_EOL.
                    $this->colors->getColoredString("Error while connection to Elasticsearch! Please check your\nconnection details in config.php and validate the settings." , 'red')
                    . PHP_EOL;
                die();
            }

          echo $this->colors->
              getColoredString('Successfully inserted  ' . Q_LIMIT . ' records to Elasticsearch, moving on...', 'green') . PHP_EOL;

          $endTime = microtime(true);
          $totalTime = ($endTime - $startTime) / 60;

          echo 'Batch Finished.' . PHP_EOL;
          echo 'Total Time Elapsed: ' . $totalTime . " Minutes.";





  }

  public function Querying($a) {
        global $ES_HOST;
        // How many queries should I be running:
        $toDoQueries = (int) ($a / Q_LIMIT) + 1;
        echo $this->colors->
            getColoredString('Based on number of your rows I have to run ' . $toDoQueries .
                ' queries.' , 'yellow') . PHP_EOL . PHP_EOL;

        echo $this->colors->
            getColoredString('



                         .i;;;;i.
                       iYcviii;vXY:
                     .YXi       .i1c.
                    .YC.     .    in7.
                   .vc.   ......   ;1c.
                   i7,   ..        .;1;
                  i7,   .. ...      .Y1i
                 ,7v     .6MMM@;     .YX,
                .7;.   ..IMMMMMM1     :t7.
               .;Y.     ;$MMMMMM9.     :tc.
               vY.   .. .nMMM@MMU.      ;1v.
              i7i   ...  .#MM@M@C. .....:71i
             it:   ....   $MMM@9;.,i;;;i,;tti
            :t7.  .....   CREATED,iii:::,,;St.
           .nC.   .....   IMMMQ..,::::::,.,czX.
          .ct:   ....... .ZMMMI..,:::::::,,:76Y.
          c2:   ......,i..Y$M@t..:::::::,,..inZY
         vov   ......:ii..c$MBc..,,,,,,,,,,..iI9i
        i9Y   ......iii:..7@MA,..,,,,,,,,,....;AA:
       iIS.  ......:ii::..;@MI....,............;Ez.
      .I9.  ......:i::::...8M1..................C0z.
     .z9;  ......:i::::,.. .i:...................zWX.
     vbv  ......,i::::,,.      ................. :AQY
    c6Y.  .,...,::::,,..:..BY... ................ :8bi
   :6S. ..,,...,:::,,,....MAHDI.. ............... .;bZ,
  :6o,  .,,,,..:::,,,..i#HAZAVEH#.................  YW2.
 .n8i ..,,,,,,,::,,,,.. tMMMMM@C:.................. .1Wn
 7Uc. .:::,,,,,::,,,,..   i1t;,..................... .UEi
 7C...::::::::::::,,,,..        ....................  vSi.
 ;1;...,,::::::,.........       ..................    Yz:
  v97,.........                                     .voC.
   izAotX7777777777777777777777777777777777777777Y7n92:
     .;CoIIIIIUAA666666699999ZZZZZZZZZZZZZZZZZZZZ6ov.

WARNING: System is about to start inserting to elasticsearch in 30 seconds.
if you wish to cancel this process hit CTRL+C now.

', 'red');
echo
$this->colors->getColoredString('
Database Name: ' . DB_NAME . '
Database Host: ' . DB_HOST . '
Database User: ' . DB_USER . '
Database Password: HIDDEN
Database Table: ' . DB_TABLE . '
Elasticsarch Index: '. ES_INDEX .'
Elasticsearch Host: '. $ES_HOST[0] .'
', 'yellow');
        sleep(30);
        echo 'Starting to query...' . PHP_EOL;
        $offset = 0;
        for ($x = 0; $x <= $toDoQueries; $x++) {
            $query = 'SELECT * FROM ' . DB_TABLE . ' LIMIT ' . Q_LIMIT . ' OFFSET ' . $offset . PHP_EOL;
            $this->execute($query);
            $this->colors->getColoredString('Finished ' . $offset , 'green' . PHP_EOL);
            $offset = $offset + Q_LIMIT;
        }
    }

}

// Coloring the CLI output for Sake of prettiness.

class Colors {
    private $foreground_colors = array();
    private $background_colors = array();

    public function __construct() {
        // Set up shell colors
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    // Returns colored string
    public function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    // Returns all foreground color names
    public function getForegroundColors() {
        return array_keys($this->foreground_colors);
    }

    // Returns all background color names
    public function getBackgroundColors() {
        return array_keys($this->background_colors);
    }
}

?>
