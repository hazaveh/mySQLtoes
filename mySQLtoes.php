<?php
/* Author: Mahdi Hazaveh <mahdi@hazaveh.net> */
require_once 'medoo.min.php';

class convertAgent
{

// Initializing default variables.

    public $idCol = 1;
    public $indexName;
    public $tableName;
    public $database;
    public $elasticSearch;
    public $colors;
    public $resume;
    public $offset = 0;
    public $logger;


    public function __construct()
    {
        global $argv;
        $this->colors = new Colors();
        if (file_exists('resume.lock') && $argv[1] != 'resume') {
            $this->resume();
        }

        if (isset($argv[1]) && $argv[1] == 'resume') {
            echo PHP_EOL . 'Resuming from previous state' . PHP_EOL;
            if (file_exists('resume.lock')) {
            $this->offset = (int) file_get_contents('resume.lock');
               } else {
                echo PHP_EOL . 'resume.lock file does not exist. Exiting...';
                die(PHP_EOL);
            }
        }

        if (strlen(DB_TABLE) == 0) {
            echo PHP_EOL . $this->colors->
                getColoredString("Database table is not defined. Please check the Config.php file.",
                    'red') . PHP_EOL;
            die();
        }

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
                getColoredString("Error While Connection to mySQL Database,\nPlease check your Database connection settings in config.php",
                    'red') . PHP_EOL;
            die();
        }
        if (USE_DB_COLUMN == TRUE) {
            $this->idCol = DB_COL;
        }
        global $ES_HOST;
        $this->logger = \Elasticsearch\ClientBuilder::defaultLogger('mySQLtoes.log');
        $this->elasticSearch = \Elasticsearch\ClientBuilder::create()->setLogger($this->logger)->setHosts($ES_HOST)->build();
    }


    // This function understands the Table Status and number of rows.
    public function understand()
    {
        echo PHP_EOL . $this->colors->
            getColoredString('mySQLtoes 1.2', 'green')
            . PHP_EOL;
        sleep(1);
        echo $this->colors->
            getColoredString('I\'m looking for the number of rows in the MySQL Table.', "green")
            . PHP_EOL;
        sleep(1);
        $totalRrows = $this->database->count(DB_TABLE);
        echo PHP_EOL . $this->colors->
            getColoredString('I found ' . $totalRrows . ' items in ' . DB_TABLE . ' table!', 'yellow')
            . PHP_EOL;
        sleep(1);
        echo PHP_EOL . $this->colors->
            getColoredString('You set a limit of ' . Q_LIMIT . ' per select query in your config file.', 'black', 'yellow')
            . PHP_EOL . PHP_EOL;
        echo $this->colors->getColoredString('Calculating number of queries to be executed.', 'green');
        sleep(1);
        echo $this->colors->getColoredString('.', 'green');
        sleep(1);
        echo $this->colors->getColoredString('.', 'green') . PHP_EOL . PHP_EOL;
        $this->Querying($totalRrows);

    }

    public function execute($x)
    {
        $startTime = microtime(true);
        $jpnResult = $this->database->query($x)->fetchAll(PDO::FETCH_ASSOC);


        echo PHP_EOL . 'Successful query.' . PHP_EOL;

        $params = ['body' => []];
        echo $this->colors->
            getColoredString('Getting ' . Q_LIMIT . ' records ready to insert.', 'yellow') . PHP_EOL;


        if ($this->idCol == DB_COL) {
            foreach ($jpnResult as $column => $content) {

                $params['body'][] = [
                    'index' => [
                        '_index' => ES_INDEX,
                        '_type' => ES_TYPE,
                        '_id' => $content[$this->idCol],
                    ]
                ];
                $params['body'][] = [$column => $content];
            }
        } else {
            foreach ($jpnResult as $column => $content) {

                $params['body'][] = [
                    'index' => [
                        '_index' => ES_INDEX,
                        '_type' => ES_TYPE,
                        '_id' => $this->idCol,
                    ]
                ];
                $params['body'][] = [$column => $content];
                $this->idCol++;
            }
        }


        try {
            $responses = $this->elasticSearch->bulk($params);
        } catch (Exception $e) {
            echo PHP_EOL .
                $this->colors->getColoredString("Error while connection to Elasticsearch! Please check your\nconnection details in config.php and validate the settings.", 'red')
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


    public function currentStatus($offset) {
        $file = fopen('resume.lock' , 'w');
        fwrite($file, $offset);
        fclose($file);
    }


    public function Querying($a, $offset = 0)
    {
        global $ES_HOST;
        // How many queries should I be running:
        $toDoQueries = (int)($a / Q_LIMIT) + 1;
        echo $this->colors->
            getColoredString('Based on number of your rows I have to run ' . $toDoQueries .
                ' queries.', 'yellow') . PHP_EOL . PHP_EOL;

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
Elasticsarch Index: ' . ES_INDEX . '
Elasticsearch Host: ' . $ES_HOST[0] . '
', 'yellow');
        sleep(30);
        echo 'Starting to query...' . PHP_EOL;

        for ($x = 0; $x <= $toDoQueries; $x++) {
            $query = 'SELECT * FROM ' . DB_TABLE . ' LIMIT ' . Q_LIMIT . ' OFFSET ' . $this->offset . PHP_EOL;
            $this->execute($query);
            $this->colors->getColoredString('Finished ' . $this->offset, 'green' . PHP_EOL);
            $this->currentStatus($this->offset);
            $this->offset = $this->offset + Q_LIMIT;
        }
    }

    public function resume() {
        echo $this->colors->getColoredString('
Error: the resume.lock file exists in the directory. this means that
this script has been stopped unexpectedly last time it was running.
if you wish to continue from the last batch please supply "resume"
switch to the script, else remove the resume.lock file to start over inserting the data.
        ', 'red');
        die(PHP_EOL);
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
