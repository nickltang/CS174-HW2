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
    // Gets pizza entries from pizza.txt file
    $data["PIZZA_ENTRIES"] = getPizzaEntries();
    $data["PIZZA_ENTRIES"] = checkForPizzaUpdates($data["PIZZA_ENTRIES"]);   

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
    $data["name"] = (isset($_REQUEST['name'])) 
        ? filter_var($_REQUEST['name'], FILTER_SANITIZE_SPECIAL_CHARS) 
        : "";
    $entries = getPizzaEntries();
    $views = $entries[$data["name"]]["visits"];
    $viewsCount = intval($views);
    $viewsCount++;
    $entries[$data["name"]]["visits"] = $viewsCount;
    file_put_contents(PIZZA_FILE, serialize($entries));

    $data["pizzaInfo"] = $entries[$data["name"]];

    $layout = (isset($_REQUEST['f']) && in_array($_REQUEST['f'], ["html"])) 
        ? $_REQUEST['f'] . "Layout" 
        : "htmlLayout";
    $layout($data, "detailView");
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

    if(array_key_exists($data["NAME"], $data["ENTRIES"])) {
        $data["currentPizza"] = $data["ENTRIES"][$data["NAME"]];
    }

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
            <link rel="stylesheet" href="css/styles.css">
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
                                        $numHearts = str_repeat("&#128151", (int) floor(log($pizzaInfo["visits"], 5)));
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
        <div>
            <a href=index.php?a=edit>
                <button class="addPieBtn">Add Pie</button></th>
            </a>      
        </div>    
    </div>
    
    <?php
}


function editView($data) {
    // Header for add pizza
    $header = "Add a Pie";
    if(isset($data["currentPizza"])) {
        $header = "Pie Editor";
    }
    ?>
    <div>
        <a href="index.php">
            <h1>Original Pizza Place</h1>
        </a>
        <h2><?=$header?></h2>
        <form action="index.php" method="post">
            <label for="name">Enter Pizza Name:</label>
            <?php
            if(isset($data["currentPizza"])) {
            ?>
                <input type="text" placeholder="Pie Name" name="name" value=<?=$data["NAME"]?>>
            <?php
            } else {
            ?>
                <input type="text" placeholder="Pie Name" name="name">
            <?php
            }
            ?>

            <br/>
            <br/>
            <label for="price">Enter Pizza Price ($):</label>
            <?php
            if(isset($data["currentPizza"])) {
            ?>
                <input type="text" placeholder="Price" name="price" value=<?=(int) $data["currentPizza"]["price"]?>>
            <?php
            } else {
            ?>
                <input type="text" placeholder="Price" name="price">
            <?php
            }
            ?>
            <div class="table-center">
                <table id="Pie Editor">
                    <tr>
                        <th>Toppings:</th>
                    </tr>
                    <tr>
                        <td> 
                            <?php
                            if(isset($data["currentPizza"]) && in_array("red-sauce", $data["currentPizza"]["toppings"])) {
                            ?> 
                                <input type="checkbox" name="toppings[red-sauce]" checked>
                            <?php
                            } else {
                            ?>
                                <input type="checkbox" name="toppings[red-sauce]">
                            <?php
                            }
                            ?>
                            <label for="red-sauce">Red Sauce</label>
                        </td>
                        <td>
                            <?php
                            if(isset($data["currentPizza"]) && in_array("green-peppers", $data["currentPizza"]["toppings"])) {
                            ?> 
                                <input type="checkbox" name="toppings[green-peppers]" checked>
                            <?php
                            } else {
                            ?> 
                                <input type="checkbox" name="toppings[green-peppers]">
                            <?php
                            }
                            ?>
                            <label for="green-peppers">Green Peppers</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            if(isset($data["currentPizza"]) && in_array("mozzarella", $data["currentPizza"]["toppings"])) {
                            ?> 
                                <input type="checkbox" name="toppings[mozzarella]" checked>
                            <?php
                            } else {
                            ?> 
                                <input type="checkbox" name="toppings[mozzarella]">
                            <?php
                            }
                            ?>
                            <label for="mozzarella">Mozzarella</label>
                        </td>
                        <td>
                            <?php
                            if(isset($data["currentPizza"]) && in_array("ham", $data["currentPizza"]["toppings"])) {
                            ?> 
                                <input type="checkbox" name="toppings[ham]" checked>
                            <?php
                            } else {
                            ?> 
                                <input type="checkbox" name="toppings[ham]">
                            <?php
                            }
                            ?>
                            <label for="ham">Ham</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            if(isset($data["currentPizza"]) && in_array("pepperoni", $data["currentPizza"]["toppings"])) {
                            ?> 
                                <input type="checkbox" name="toppings[pepperoni]" checked>
                            <?php
                            } else {
                            ?> 
                                <input type="checkbox" name="toppings[pepperoni]">
                            <?php
                            }
                            ?>
                            <label for="pepperoni">Pepperoni</label>
                        </td>
                        <td>
                            <?php
                            if(isset($data["currentPizza"]) && in_array("mushrooms", $data["currentPizza"]["toppings"])) {
                            ?> 
                                <input type="checkbox" name="toppings[mushrooms]" checked>
                            <?php
                            } else {
                            ?> 
                                 <input type="checkbox" name="toppings[mushrooms]">
                            <?php
                            }
                            ?>
                            <label for="mushrooms">Mushrooms</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            if(isset($data["currentPizza"]) && in_array("pineapple", $data["currentPizza"]["toppings"])) {
                            ?> 
                                <input type="checkbox"  name="toppings[pineapple]" checked>
                            <?php
                            } else {
                            ?> 
                                <input type="checkbox" name="toppings[pineapple]">
                            <?php
                            }
                            ?>
                            <label for="pineapple">Pineapple</label>
                        </td>
                        <td>
                            <?php
                            if(isset($data["currentPizza"]) && in_array("anchovies", $data["currentPizza"]["toppings"])) {
                            ?> 
                                <input type="checkbox" name="toppings[anchovies]" checked>
                            <?php
                            } else {
                            ?> 
                                <input type="checkbox" name="toppings[anchovies]">
                            <?php
                            }
                            ?>
                            <label for="anchovies">Anchovies</label>
                        </td>
                    </tr>
                </table>
                <div>
                    <button class="create" name="add">Create</button>
                </div>
            </div>    
        </form>

    <?php    
}

function detailView($data) {
    ?>
    <div>
        <a href="index.php">
            <h1> Original Pizza Place</h1>
        </a>
        <?php
        if(!empty($data["name"])) {
        ?>
            <div class="text-center">
                <h2> <?=$data["name"]?> </h2>
                <h3>Price: <?=$data["pizzaInfo"]["price"]?></h3>
                <?php
                foreach($data["pizzaInfo"]["toppings"] as $key => $value) {
                ?>
                    <li> <?=$value?> </li>
                <?php
                }
                ?>
                <a href=index.php>Back</a>
            </div>
            <div id="pizza-style">
                <div id="crust">
                        <?php
                        if(isset($data["pizzaInfo"]) && in_array("red-sauce", $data["pizzaInfo"]["toppings"])) {
                        ?> 
                            <div id="pizza-sauce">
                        <?php
                        }    
                        ?>
                        <div id="cheese"></div>
                            <?php
                            if(isset($data["pizzaInfo"]) && in_array("pepperoni", $data["pizzaInfo"]["toppings"])) {
                            ?> 
                                <div id="pepperoni-one"></div>
                                <div id="pepperoni-two"></div>
                                <div id="pepperoni-three"></div>
                            <?php
                            }
                            ?>
                            <?php
                            if(isset($data["pizzaInfo"]) && in_array("mushrooms", $data["pizzaInfo"]["toppings"])) {
                            ?>
                                <div id="mushroom-one"></div>
                                <div id="mushroom-two"></div>
                                <div id="mushroom-three"></div>
                            <?php
                            }
                            ?>
                            <?php
                            if(isset($data["pizzaInfo"]) && in_array("mozzarella", $data["pizzaInfo"]["toppings"])) {
                            ?>
                                <div id="mozzarella-one"></div>
                                <div id="mozzarella-two"></div>
                            <?php
                            }
                            ?>
                            <?php
                            if(isset($data["pizzaInfo"]) && in_array("pineapple", $data["pizzaInfo"]["toppings"])) {
                            ?>
                                <div id="pineapple-one"></div>
                                <div id="pineapple-two"></div>
                            <?php
                            }
                            ?>  
                            <?php
                            if(isset($data["pizzaInfo"]) && in_array("green-peppers", $data["pizzaInfo"]["toppings"])) {
                            ?>
                                <div id="green-pepper-one"></div>
                                <div id="green-pepper-two"></div>
                                <div id="green-pepper-three"></div>
                            <?php
                            }
                            ?>
                            <?php
                            if(isset($data["pizzaInfo"]) && in_array("ham", $data["pizzaInfo"]["toppings"])) {
                            ?>
                                <div id="ham-one"></div>
                                <div id="ham-two"></div>
                                <div id="ham-three"></div>
                            <?php
                            }
                            ?> 
                            <?php
                            if(isset($data["pizzaInfo"]) && in_array("anchovies", $data["pizzaInfo"]["toppings"])) {
                            ?> 
                                <div id="anchovies-one"></div>
                                <div id="anchovies-two"></div>
                                <div id="anchovies-three"></div>
                            <?php
                            }
                            ?>   
                            </div>
                        <?php  
                        if(isset($data["pizzaInfo"]) && in_array("red-sauce", $data["pizzaInfo"]["toppings"])) {
                        ?> 
                            </div> 
                        <?php
                        }    
                        ?>      
            </div>
        <?php
        }
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
        <form action="index.php">
            <a href=index.php?delete=true&name=<?=urlencode($data["NAME"])?>>
                <button type="button">Confirm</button>
            </a>
            <a href=index.php>
                <button type="button">Cancel</button>
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


function checkForPizzaUpdates($entries) {
    // Add/Edit Pizza Logic
    if(array_key_exists("add", $_POST) && $_POST["name"] != "" && array_key_exists("toppings", $_POST)) {
        // Format toppings into array
        $addToppings = [];
        foreach($_POST["toppings"] as $topping => $status) {
            if($status == "on")
                array_push($addToppings, $topping);
        }

        // Sanitize inputs
        $sanitizedName = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);
        $sanitizedPrice = filter_var($_POST["price"], FILTER_SANITIZE_SPECIAL_CHARS);
        
        // Check for null price
        if($sanitizedPrice == "")
            $sanitizedPrice = 0;

        // Add or update pizza in entries array
        if(array_key_exists($sanitizedName, $entries)) {
            $entries[$sanitizedName]["price"] = $sanitizedPrice;
            $entries[$sanitizedName]["toppings"] = $addToppings;
        } else {
            $entries[$sanitizedName] = [
                "price" => $sanitizedPrice,
                "visits" => 0,
                "toppings" => $addToppings
            ];    
        }        
    }

    // Delete Pizza Logic
    if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'true' && isset($_REQUEST['name'])) {
        unset($entries[$_REQUEST['name']]); 
    }

    file_put_contents(PIZZA_FILE, serialize($entries));

    return $entries;
}