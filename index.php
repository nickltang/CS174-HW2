<?php

// Specify location of file containing all saved pizzas
define("PIZZA_FILE", "pizza.txt");

// Determine which activity to perform and call its controller
$activity = (isset($_REQUEST['a']) && in_array($_REQUEST['a'], ["landing", "edit", "detail", "confirm"])) 
    ? $_REQUEST['a'] . "Controller" 
    : "landingController";
$activity();


/**
 * Used to process perform activities realated to the blog landing page
 */
function landingController()
{
    // Dummy data
    $data["PIZZA_ENTRIES"] = [
        'Fromage Delight' => [
            'price' => 12,
            'visits' => 30,
            'ingredients' => ['cheese', 'pepperoni']
        ],
        'Peppy Pizzazz' => [
            'price' => 30,
            'visits' => 5,
            'ingredients' => []
        ]
    ];
    // Gets pizza entries from pizza.txt file
    // $data["PIZZA_ENTRIES"] = getPizzaEntries();

    $data["PIZZA_ENTRIES"] = checkForPizzaUpdates($data["PIZZA_ENTRIES"]);   


    var_dump($_POST);

    $layout = (isset($_REQUEST['f']) && in_array($_REQUEST['f'], ["html"])) 
        ? $_REQUEST['f'] . "Layout" 
        : "htmlLayout";
    $layout($data, "menuView");
}


/**
 * Used to process perform activities realated to the blog landing page
 */
function detailController()
{
    $data["NAME"] = (isset($_REQUEST['name'])) 
        ? filter_var($_REQUEST['name'], FILTER_SANITIZE_SPECIAL_CHARS) 
        : "";
    $entries = getPizzaEntries();

    $data["POST"] = $entries[$data["TITLE"]];
    

    // maybe in the future could modify so also support RSS out
    $layout = (isset($_REQUEST['f']) && in_array($_REQUEST['f'], ["html"])) 
        ? $_REQUEST['f'] . "Layout" 
        : "htmlLayout";
    $layout($data, "editView");
}

/**
 * Used to process perform activities realated to the blog landing page
 */
function confirmController()
{
    $data["NAME"] = (isset($_REQUEST['name'])) 
        ? filter_var($_REQUEST['name'], FILTER_SANITIZE_SPECIAL_CHARS) 
        : "";
    $data["ENTRIES"] = getPizzaEntries();
    $data["ENTRIES"] = deletePizzaEntry($data);
    
    $layout = (isset($_REQUEST['f']) && in_array($_REQUEST['f'], ["html"])) 
        ? $_REQUEST['f'] . "Layout" 
        : "htmlLayout";
    $layout($data, "confirmView");
}

/**
 * Used to process perform activities realated to the blog landing page
 */
function editController()
{
    $data["NAME"] = (isset($_REQUEST['name'])) 
        ? filter_var($_REQUEST['name'], FILTER_SANITIZE_SPECIAL_CHARS) 
        : "";
    $data["ENTRIES"] = getPizzaEntries(); 

    $layout = (isset($_REQUEST['f']) && in_array($_REQUEST['f'], ["html"])) 
        ? $_REQUEST['f'] . "Layout" 
        : "htmlLayout";
    $layout($data, "editView");
}

function htmlLayout($data, $view)
{
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <title>Original Pizza Place </title>
        </head>
        <body>
        <?php
            $view($data);
        ?>
        </body>
    </html>
    <?php
}


function menuView($data) {
    ?>
    <div>
        <a href="index.php">
            <h1> Original Pizza Place</h1>
        </a>
        <h2> Menu </h2>
        <?php
        if(!empty($data["PIZZA_ENTRIES"])) {
        ?>
            <div class="table-center">
                <table id="Menu Table">
                    <tr>
                        <th>Pizza</th>
                        <th>Price</th> 
                        <th>Popularity</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    foreach($data["PIZZA_ENTRIES"] as $name => $pizzaInfo) {
                    ?>
                        <div>
                            <tr>
                                <td>
                                    <a href="index.php?a=detail&name=<?=urlencode($name)?>"><?=$name?></a> 
                                </td>
                                <td>$<?=$pizzaInfo["price"]?></td>
                                <td>
                                    <?php
                                        $numHearts = str_repeat("&#128151", log($pizzaInfo["visits"], 5));
                                    ?>
                                    <?=$numHearts?>
                                </td> 
                                <td> 
                                    <a href=index.php?a=edit&name=<?=urlencode($name)?>&pizzaInfo=<?=urlencode(serialize($pizzaInfo))?>>
                                        <button class="editbtn">✏️</button>
                                    </a>
                                </td>
                                <td> 
                                    <a href=index.php?a=confirm&name=<?=urlencode($name)?>>
                                        <button class="trashbtn">🗑</button>
                                    </a>    
                                </td>
                            </tr>
                        </div>
                    <?php
                    }
                    ?>
                </table>
            </div>
        <?php
        }
        ?>
        <div class="text-center">
            <?php
                if(!empty($data["PIZZA_ENTRIES"])) {
            ?>
                <a href=index.php?a=edit&name=<?=urlencode($name)?>>
                    <button class="addPieBtn">Add Pie</button></th>
                </a>
            <?php
                }
                else
            ?>
                <a href=index.php?a=edit>
                    <button class="addPieBtn">Add Pie</button></th>
                </a>        
        </div>    
    </div>
    
    <?php
}

function editView($data) {
    ?>
    <div>
        <a href="index.php">
            <h1> Original Pizza Place</h1>
        </a>
        <h2> Pie Editor </h2>
        <form action="index.php" method="post">
            <input type="text" placeholder="Pie Name" name="name">
            <input type="text" placeholder="Price" name="price">
            <div class="table-center">
                <table id="Pie Editor">
                    <tr>
                        <th>Toppings:</th>
                    </tr>
                    <tr>
                        <td> 
                            <input type="checkbox" id="red-sauce" name="red-sauce">
                            <label for="red-sauce">Red Sauce</label>
                        </td>
                        <td>
                            <input type="checkbox" id="green-peppers" name="green-peppers">
                            <label for="green-peppers">Green Peppers</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" id="mozarella" name="mozarella">
                            <label for="mozarella">Mozarella</label>
                        </td>
                        <td>
                            <input type="checkbox" id="ham" name="ham">
                            <label for="ham">Ham</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" id="pepperoni" name="pepperoni">
                            <label for="pepperoni">Pepperoni</label>
                        </td>
                        <td>
                            <input type="checkbox" id="mushrooms" name="mushrooms">
                            <label for="mushrooms">Mushrooms</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" id="pineapple" name="pineapple">
                            <label for="pineapple">Pineapple</label>
                        </td>
                        <td>
                            <input type="checkbox" id="anchovies" name="anchovies">
                            <label for="anchovies">Anchovies</label>
                        </td>
                    </tr>
                </table>
                <div class="text-center">
                    <button class="create" name="add">Create</button>
                </div>
            </div>    
        </form>

    <?php    
}

function detailView() {
    ?>

    <?php
}

function confirmView($data) {
    ?>
    <div>
        <a href="index.php">
            <h1> Original Pizza Place</h1>
        </a>
        <p>Are you sure you want to delete <b><?=$data["NAME"]?></b>?</p>
        <form method="post" action="index.php">
            <a href="index.php">
                <input type="button" name="delete">Confirm</button>
            </a>
            <a href=index.php?name=<?urlencode($data["NAME"])?>>
                <input type="button">Cancel</button>
            </a>    
        </form>
        
    </div>
    <?php
}


/**
 * Used to get an array of all the blog entries currently stored on disk.
 *
 * @return array blog entries [ title1 => post1, title2 => post2 ...] if 
 *   file exists and unserializable, [] otherwise
 */
function getPizzaEntries()
{
    if (file_exists(PIZZA_FILE)) {
        $entries = unserialize(file_get_contents(PIZZA_FILE));
        if ($entries) {
            return $entries;
        }
    }
    return [];
}

/**
 * Determines if a new blog post was sent from landing form. If so,
 * adds the new post, to the end of a current list of posts, and saves the
 * serialized result to BLOG_FILE
 *
 * @param array $entries an array of current blog entries:
 *  blog entries [ title1 =>post1, title2 => post2 ...]
 * @return array blog entries (updated) [ title1 => post1, title2 => post2 ...]
 *  if file exists and unserializable, [] otherwise
 */
function addPizzaEntry($entries)
{
    $name = (isset($_REQUEST['name'])) 
        ? filter_var($_REQUEST['name'], FILTER_SANITIZE_SPECIAL_CHARS) 
        : "";
    $pizzaInfo = (isset($_REQUEST['pizzaInfo'])) 
        ? filter_var($_REQUEST['pizzaInfo'], FILTER_SANITIZE_SPECIAL_CHARS) 
        : "";
    
    if ($name == "" || $pizzaInfo == "") {
        return $entries;
    }

    if(!array_key_exists($name, $entries))
        $entries = array_merge([$name => $pizzaInfo], $entries);
    else
        $entries[$name] = [$pizzaInfo];

    file_put_contents(PIZZA_FILE, serialize($entries));

    return $entries;
}


function deletePizzaEntry($data)
{
    // Check for pizza name
    $name = (isset($_REQUEST['NAME'])) ?
        filter_var($_REQUEST['NAME'], FILTER_SANITIZE_SPECIAL_CHARS) : "";
    
    if ($name == "") {
        return $data["ENTRIES"];
    }
    if(isset($_POST["delete"])) {
       unset($data["ENTRIES"][$name]); 
       echo "Deleting";
    }
        

    file_put_contents(PIZZA_FILE, serialize($data["ENTRIES"]));
    
    return $data["ENTRIES"];
}

function checkForPizzaUpdates($entries) {
    if(array_key_exists("add", $_POST)) {
        echo "here";
    }
    // $name = (isset($_REQUEST['name'])) 
    //     ? filter_var($_REQUEST['name'], FILTER_SANITIZE_SPECIAL_CHARS) 
    //     : "";
    // $pizzaInfo = (isset($_REQUEST['pizzaInfo'])) 
    //     ? filter_var($_REQUEST['pizzaInfo'], FILTER_SANITIZE_SPECIAL_CHARS) 
    //     : "";
    
    // if ($name == "" || $pizzaInfo == "") {
    //     return $entries;
    // }

    // if(!array_key_exists($name, $entries))
    //     $entries = array_merge([$name => $pizzaInfo], $entries);
    // else
    //     $entries[$name] = [$pizzaInfo];

    // file_put_contents(PIZZA_FILE, serialize($entries));

    return;
}