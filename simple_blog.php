<?php
/**
 * simple_blog.php
 * This program is used to maintain a simple web blog. It has two
 * pages a landing page, where people can add new blog posts as
 * well as see a list of previous posts and an entry page where
 * people can read an old post 
 */


// Specify location of file containing all the blog posts
define("BLOG_FILE", "blog.txt");

// Determine which activity to perform and call it
$activity = (isset($_REQUEST['a']) && in_array($_REQUEST['a'], ["main", "entry"])) 
    ? $_REQUEST['a'] . "Controller" 
    : "mainController";
$activity();


/**
 * Used to process perform activities realated to the blog landing page
 */
function mainController()
{
    // Gets blog entries from blog.txt file
    $data["BLOG_ENTRIES"] = getBlogEntries();

    // Serializes blog posts
    $data["BLOG_ENTRIES"] = processNewBlogEntries($data["BLOG_ENTRIES"]);

    // maybe in the future could modify so also support RSS out
    $layout = (isset($_REQUEST['f']) && in_array($_REQUEST['f'], ["html"])) 
        ? $_REQUEST['f'] . "Layout" 
        : "htmlLayout";
    $layout($data, "landingView");
}


/**
 * Used to set up and then display the view corresponding to a single blog
 * post.
 */
function entryController()
{
    $data["TITLE"] = (isset($_REQUEST['title'])) ? 
        filter_var($_REQUEST['title'], FILTER_SANITIZE_STRING) : "";
    $entries = getBlogEntries();

    if (!isset($entries[$data["TITLE"]])) {
        mainController();
        return;
    }

    $data["POST"] = $entries[$data["TITLE"]];

    $layout = (isset($_REQUEST['f']) && in_array($_REQUEST['f'], ["html"])) 
        ? $_REQUEST['f'] . "Layout" 
        : "htmlLayout";
    $layout($data, "entryView");
}


/**
 * Used to output the top and bottom boilerplate of a Web page. Within
 * the body of the document the passed $view is draw
 *
 * @param array $data an associative array of field variables which might
 *  be echo'd by either this layout in the title, or by the view that is
 *  draw in the body
 * @param string $view name of view function to call to draw body of web page
 */
function htmlLayout($data, $view)
{
    ?><!DOCTYPE html>
    <html>
        <head>
            <title>Simple Blog 
                <?php if (!empty($data['TITLE'])) {
                    echo ":" . $data['TITLE'];
                } ?>
            </title>
        </head>
        <body>
            <?php
            $view($data);
            ?>
        </body>
    </html><?php
}


/**
 * Used to draw the main landing page with blog form on it as well as previous
 * blog posts
 *
 * @param array $data an associative array of field variables which might
 *  be echo'd by this function. In this case, we will use $data["BLOG_ENTRIES"]
 *  to output old blog entries
 */
function landingView($data)
{
    ?>
    <h1><a href="simple_blog.php">Simple Blog</a></h1>
    <h2>New Blog Entry</h2>
    <form>
        <div>
            <label for='post-title'>Title</label>:
            <input id='post-title' name="title" placeholder="Post Title" type="text" />
        </div>
        <div>
            <label for='post-body'>Post</label>:<br />
            <textarea id='post-body' name="post" rows="30" cols="80"
            placeholder="Type your blog post here" ></textarea>
        </div>
        <div>
            <button>Save</button>
        </div>
    </form>
    <h2>Previous Entries</h2>
    <?php
    if (!empty($data["BLOG_ENTRIES"])) {
        foreach ($data["BLOG_ENTRIES"] as $title => $post) {
            ?><div>
                <a href="simple_blog.php?a=entry&title=<?=urlencode($title)?>">
                    <?=$title ?>
                </a>
            </div><?php
        }
    }
}

/**
 * Used to output to the browser an individual blog entry
 *
 * @param array $data an associative array of field variables which might
 *  be echo'd by this function. In this case, we will use $data["TITLE"]
 *  and $data['POST'] which contain the blog post
 */
function entryView($data)
{
    ?>
    <h1><a href="simple_blog.php">Simple Blog</a> : <?=$data['TITLE'] ?></h1>
    <h2><?=$data['TITLE'] ?></h2>
    <div>
        <?=$data['POST'] ?>
    </div>
    <?php
}


/**
 * Used to get an array of all the blog entries currently stored on disk.
 *
 * @return array blog entries [ title1 => post1, title2 => post2 ...] if 
 *   file exists and unserializable, [] otherwise
 */
function getBlogEntries()
{
    if (file_exists(BLOG_FILE)) {
        $entries = unserialize(file_get_contents(BLOG_FILE));
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
function processNewBlogEntries($entries)
{
    $title = (isset($_REQUEST['title'])) ?
        filter_var($_REQUEST['title'], FILTER_SANITIZE_STRING) : "";
    $post = (isset($_REQUEST['post'])) ?
        filter_var($_REQUEST['post'], FILTER_SANITIZE_STRING) : "";
    if ($title == "" || $post == "") {
        return $entries;
    }

    $entries = array_merge([$title => $post], $entries);

    file_put_contents(BLOG_FILE, serialize($entries));

    return $entries;
}