<?php
namespace cymapgt\core\utility\console;

use cymapgt\core\utility\console\helper\ConsoleEcho;
use cymapgt\Exception\NetConsoleException;

/**
 */
class DemoApi implements abstractclass\NetConsoleInterface
{
    private static $_binomialNomenclature = array (
        'Panthera' => array (
            'leo' => array (
                'en' => 'lion',
                'sw' => 'simba',
                'jl' => 'sibuor'
            ),
            'pardus' => array (
                'en' => 'leopard',
                'sw' => 'chui',
                'jl' => 'kwach'
            )
        )
    );
    
    private $_animalNames = array (
        
    );
    
       /**
     * Enable fine grained processing of searching API functionality It also adds functionality
        * documentation for the methos
     * 
     * Cyril Ogana <cogana@gmail.com> 2015-08-20
     * 
     * @param string $serviceName - The service name as registered in the API service
     * @param string $methodName - Optional methodname to trim down the query
     * 
     * @return array
     * 
     * @access public
     * @static
     */
    public static function getUsage($serviceName, $methodName = null) {
        if ($methodName) {
            $serviceNameArr = array_keys(self::listFunction());

            if (
                ! (is_array($serviceNameArr))
                || ! (array_search($methodName, $serviceNameArr) !== false)
            ) {
                throw new NetConsoleException("The method name $methodName is not a member of $serviceName netconsole interface!");
            }

            $listOfFunctionsArr = self::listFunction();
            $listOfFunctions = array($methodName => $listOfFunctionsArr[($methodName)]);
        } else {
            $listOfFunctions = self::listFunction();
        }

        $listOfMethodsArr = array();
        
        //validation
        foreach ($listOfFunctions as $listOfMethodsNameAttribute => $listOfMethods) {
            //list of functions args must be set and an array
            if (
                !(isset($listOfMethods['args']))
                || (!is_array($listOfMethods['args']))
            ) {
                throw new NetConsoleException('The list of functions in API definition must be an array');
            }
            
            $listOfMethodsArr[] = $listOfMethodsNameAttribute;
        }
        
        //Load the documentation
        foreach ($listOfMethodsArr as $methodNameIterated) {
            foreach ($listOfFunctions[($methodNameIterated)]['args'] as $functionArgName => $functionArgDef) {
                $listFunctionDocumentationArr = self::listFunctionDocumentation();
                $listFunctionDocumentation = $listFunctionDocumentationArr[($methodNameIterated)]['docs'][($functionArgName)];
                $listOfFunctions[($methodNameIterated)]['args'][($functionArgName)]['docs'] = $listFunctionDocumentation;         
            }
            
            $listFunctionCommands = $listFunctionDocumentationArr[($methodNameIterated)]['commands'];
            $listOfFunctions[($methodNameIterated)]['commands'] = $listFunctionCommands;
            $listFunctionFlags = $listFunctionDocumentationArr[($methodNameIterated)]['flags'];
            $listOfFunctions[($methodNameIterated)]['flags'] = $listFunctionFlags;               
        }

        return $listOfFunctions;
    }
    
    /**
     *  List functions supported by this console service
     * 
     * @return array
     * @static
     */
    public static function listFunction() {
        return array (
            'bnToName' => array (
                'method' => 'help',
                'args' => array (
                    'genus' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                            'alphaonly' => array()
                        )
                    ),
                    'species' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                            'alphaonly' => array()                            
                        )
                    ),
                    'language' => array (
                        'type' => 'varNull',
                        'params' => array (
                            'alphaonly' => array()                            
                        )
                    )
                )
            ),
            'nameToBn' => array (
                'method' => 'add',
                'args' => array (
                    'name' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                        )
                    )
                )
            )            
        );
    }

    /**
     *  Provide documentation for the function list
     * 
     * @return array
     * @static
     */
    public static function listFunctionDocumentation() {
        return array (
            'bnToName' => array (
                'commands' => array (
                    'genus',
                    'species'
                ),
                'flags' => array (
                    'language' => array (
                        'flagid' => 'lang',
                        'flag' => '-lang="..."',
                        'required' => false
                    )
                ),
                'docs' => array (
                    'genus' => array (
                        1 => "In biology, a genus /ˈdʒiːnəs/ (plural: genera) is a taxonomic rank used in the biological classification of living and fossil organisms.".PHP_EOL.PHP_EOL,
                        2 => "In the hierarchy of biological classification, genus comes above species and below family. In binomial nomenclature, the genus name forms the first part of the binomial species name for each species within the genus. E.G., Pongo pygmaeus and Pongo abelii are two species within the genus Pongo. Pongo is a genus within the family Hominidae.".PHP_EOL.PHP_EOL,
                        3 => "The rules for scientific names are laid down in the Nomenclature Codes; depending on the kind of organism and the Kingdom it belongs to, a different Code may apply, with different rules, laid down in a different terminology. The advantages of scientific over common names are that they are accepted by speakers of all languages, and that each species has only one name. This reduces the confusion that may arise from the use of a common name to designate different things in different places (example elk), or from the existence of several common names for a single species. It is possible for a genus to be assigned to a kingdom governed by one particular Nomenclature Code by one taxonomist, while other taxonomists assign it to a kingdom governed by a different Code, but this is the exception, not the rule.".PHP_EOL.PHP_EOL
                    ),
                    'species' => array (
                        1 => 'In biology, a species (abbreviated sp., with the plural form species abbreviated spp.) is one of the basic units of biological classification and a taxonomic rank.'.PHP_EOL.PHP_EOL,
                        2 => 'A species is often defined as the largest group of organisms where two hybrids are capable of reproducing fertile offspring, typically using sexual reproduction. While in many cases this definition is adequate, the difficulty of defining species is known as the species problem. Differing measures are often used, such as similarity of DNA, morphology, or ecological niche. Presence of specific locally adapted traits may further subdivide species into "infraspecific taxa" such as subspecies (and in botany other taxa are used, such as varieties, subvarieties, and formae).'.PHP_EOL.PHP_EOL,
                        3 => 'Species hypothesized to have the same ancestors are placed in one genus, based on similarities. The similarity of species is judged based on comparison of physical attributes, and where available, their DNA sequences. All species are given a two-part name, a "binomial name", or just "binomial". The first part of a binomial is the generic name, the genus to which the species belongs. The second part is either called the specific name (a term used only in zoology) or the specific epithet (the term used in botany, which can also be used in zoology). For example, Boa constrictor is one of four species of the Boa genus. While the genus gets capitalized, the species name does not. The binomial is written in italics when printed and underlined when handwritten. A usable definition of the word "species" and reliable methods of identifying particular species are essential for stating and testing biological theories and for measuring biodiversity, though other taxonomic levels such as families may be considered in broad-scale studies.[1] Extinct species known only from fossils are generally difficult to assign precise taxonomic rankings, which is why higher taxonomic levels such as families are often used for fossil-based studies.[1][2] The total number of non-bacterial and non-archaeal species in the world has been estimated at 8.7 million,[3][4] with previous estimates ranging from two million to 100 million.[5]'.PHP_EOL.PHP_EOL
                    ),
                    'language' => array (
                        1 => 'Select the language with which the nomenclature should be provided'.PHP_EOL.PHP_EOL,
                        2 => 'The language should be in 2 character format e.g. en for English'.PHP_EOL.PHP_EOL,
                        3 => 'The default language is english. Supported languages are Swahili and Jaluo'.PHP_EOL.PHP_EOL
                    )
                )
            ),
            'nameToBn' => array (
                'commands' => array (
                    'name'
                ),
                'flags' => array (
                ),
                'docs' => array (
                    'name' => array (
                        1 => "Many animals, particularly domesticated, have been given specific names for males, females, young, and groups.".PHP_EOL.PHP_EOL,
                        2 => "The best known source of many of the bizarre words used for collective groupings of animals is The Book of Saint Albans, an essay on hunting published in 1486 and attributed to Dame Juliana Berners.[1]".PHP_EOL.PHP_EOL,
                        3 => "Most terms used here may be found in common dictionaries and general information web sites.".PHP_EOL.PHP_EOL
                    )
                )
            )            
        );
    }
    
    /**
     * Execute a particular function, together with provided args
     *                             
     * Cyril Ogana <cogana@gmail.com> - 2015-08-13
     * 
     * @param string $functionName - Name of function (method) to execute
     * @param array $serviceCommands- Commands issued to the service
     * @param array $commandFlags - Flags issued to the service
     * 
     * @return bool
     * 
     * @access public
     * @static
     */             
    public static function executeFunction($functionName, $serviceCommands = array(), $commandFlags = array()) {
        switch ($functionName) {
            case 'bnToName':
                    $genus   = $serviceCommands[2];
                    $species = $serviceCommands[3];
                    
                    $binomialNomenclature = self::$_binomialNomenclature;
                    if (!(isset($binomialNomenclature[($genus)][($species)]))) {
                        echo ConsoleEcho::netEcho('The species you have indicated does not exists'.ConsoleEcho::netEol());
                    } else {
                        $animalName = $binomialNomenclature[($genus)][($species)]['en'];
                        echo ConsoleEcho::netEcho("The binomial nomenclature $genus $species belongs to $animalName" . ConsoleEcho::netEol());
                    }
                break;
            case 'nameToBn':
                
                break;
        }
    }
    
    /**
     * Default action when help is called
     * 
     * @param array $arguments - Arguments
     * 
     * @return string
     */
    public static function helpDefaultAction($arguments) {
        
    }
}
