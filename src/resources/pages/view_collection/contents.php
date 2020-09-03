<?PHP

    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\HTML;
    use DynamicalWeb\Javascript;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\Collection;
    use MongoDB\Model\BSONArray;
    use MongoDB\Model\BSONDocument;

    HTML::importScript("select_database");

    /** @var Collection $Collection */
    $Collection = DynamicalWeb::getMemoryObject("collection");

    $is_filtering = false;
    $filter = [];
    $filter_value = "";
    if(isset($_GET["filter_by"]))
    {
        if(isset($_GET["filter_value"]))
        {
            $filter_value = $_GET["filter_value"];
            $filter = [$_GET["filter_by"] => $_GET["filter_value"]];
            $is_filtering = true;
        }
    }

    // Calculate the pagination (w0w, so hard!11, this is the most difficult part!!11!1)
    $RecordsPerPage = 500; // The amount of items per page

    if(isset($_GET["limit"]))
    {
        if((int)$_GET["limit"] > 0)
        {
            $RecordsPerPage = (int)$_GET["limit"];
        }
    }

    $Pages = 1; // The current amount of pages (for now)
    $TotalDocuments = $Collection->countDocuments(); // How many documents we are dealing with

    if($TotalDocuments > 0)
    {
        // Do maths!!!
        $Pages = (($TotalDocuments - 1) / $RecordsPerPage) + 1;
    }

    // The amount of contents to skip depending on the current page
    $SkipContents = 0;
    $CurrentPage = 1;

    if(isset($_GET["page"]))
    {
        if((int)$_GET["page"] > $Pages)
        {
            $CurrentPage = $Pages;
        }
        elseif((int)$_GET["page"] < 1)
        {
            $CurrentPage = 1;
        }
        else
        {
            $CurrentPage = (int)$_GET["page"];
        }
    }

    $SkipContents = (int)(($CurrentPage - 1) * $RecordsPerPage);

    // Get the contents of the collection
    $Cursor = $Collection->find($filter, ["limit" => $RecordsPerPage, "skip" => $SkipContents]);
    $Columns = [];
    $Results = [];

    /** @var BSONDocument $value */
    foreach($Cursor as $BSONDocument)
    {
        $ResultArray = array();
        foreach($Columns as $column)
        {
            $ResultArray[$column] = "null";
        }

        foreach($BSONDocument as $item => $value)
        {
            if(in_array($item, $Columns) == false)
            {
                $Columns[] = $item;
            }
            $Value = $value;

            switch(gettype($value))
            {
                case "string":
                case "integer":
                    $ResultArray[$item] = $value;
                    break;

                case "object":
                    switch(get_class($value))
                    {
                        case "MongoDB\BSON\ObjectId":
                            $ResultArray[$item] = (string)$value;
                            break;

                        case "MongoDB\Model\BSONArray":
                            /** @var BSONArray $BSONArray */
                            $BSONArray = $value;
                            $ResultArray[$item] = json_encode($BSONArray->jsonSerialize(), JSON_PRETTY_PRINT);
                            break;

                        case "MongoDB\Model\BSONDocument":
                            /** @var BSONDocument $BSONDocument */
                            $BSONDocument = $value;
                            $ResultArray[$item] = json_encode($BSONDocument->jsonSerialize(), JSON_PRETTY_PRINT);
                            break;

                        case "MongoDB\BSON\UTCDateTime":
                            /** @var UTCDateTime $UTCDateTime */
                            $UTCDateTime = $value;
                            $ResultArray[$item] = gmdate("F j, Y, g:i a", (string)$UTCDateTime);
                            break;

                        default:
                            $ResultArray[$item] = get_class($value);
                            break;
                    }
            }
        }

        $Results[] = $ResultArray;
    }

?>
<!doctype html>
<html lang="<?PHP HTML::print(APP_LANGUAGE_ISO_639); ?>">
    <head>
        <?PHP HTML::importSection('header'); ?>
        <title><?PHP HTML::print("MongoViewer"); ?></title>
    </head>

    <body>
        <?PHP HTML::importSection("navigation"); ?>
        <div class="container-fluid">
            <div class="row">
                <main role="main" class="col-12">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                        <h1 class="h2"><?PHP HTML::print($_GET["db"] . "." . $_GET["col"]); ?></h1>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header">
                                    <h4 class="my-0 font-weight-normal">Documents</h4>
                                </div>
                                <div class="card-body">
                                    <h1 class="card-title">
                                        <?PHP print($Collection->countDocuments()); ?>
                                    </h1>
                                    <ul class="list-unstyled mt-3">
                                        <li><?PHP print("Estimated " . $Collection->estimatedDocumentCount()); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-9">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header">
                                    <h4 class="my-0 font-weight-normal">Search</h4>
                                </div>
                                <?PHP
                                    if($is_filtering == false)
                                    {
                                        ?>
                                        <form method="GET" action="<?PHP DynamicalWeb::getRoute("view_collection", array("db" => $_GET["db"], "col" => $_GET["col"]), true); ?>">
                                        <?PHP
                                    }
                                ?>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                <label for="filter_by">Filter By</label>
                                                <?PHP
                                                if(count($Columns) == 0)
                                                {
                                                    ?>
                                                    <select class="form-control" id="filter_by" name="filter_by">
                                                        <option>No Values</option>
                                                    </select>
                                                    <?PHP
                                                }
                                                else
                                                {
                                                    ?>
                                                    <select class="form-control" id="filter_by" name="filter_by">
                                                        <?PHP
                                                        foreach($Columns as $column)
                                                        {
                                                            HTML::print("<option value=\"", false);
                                                            HTML::print($column);
                                                            HTML::print("\"", false);

                                                            if(isset($_GET["filter_by"]))
                                                            {
                                                                if($_GET["filter_by"] == $column)
                                                                {
                                                                    HTML::print(" selected=\"selected\"", false);
                                                                }
                                                            }
                                                            HTML::print(">", false);
                                                            HTML::print($column);
                                                            HTML::print("</option>", false);
                                                        }
                                                        ?>
                                                    </select>
                                                    <?PHP
                                                }
                                                ?>

                                            </div>
                                            <label for="filter_value">Value</label>
                                            <input type="text" class="form-control" id="filter_value" name="filter_value" placeholder="Filter Value (foo, bar, 18...)" value="<?PHP HTML::print($filter_value); ?>">
                                            <input type="text" hidden="hidden" class="form-control" id="db" name="db" value="<?PHP HTML::print($_GET["db"]); ?>">
                                            <input type="text" hidden="hidden" class="form-control" id="col" name="col" value="<?PHP HTML::print($_GET["col"]); ?>">
                                            <input type="text" hidden="hidden" class="form-control" id="limit" name="limit" value="<?PHP HTML::print($RecordsPerPage); ?>">
                                        </div>

                                        <?PHP
                                        if($is_filtering)
                                        {
                                            $Parameters = $_GET;
                                            unset($Parameters["filter_value"]);
                                            unset($Parameters["filter_by"]);
                                            ?>
                                            <button class="btn btn-warning" onclick="location.href='<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>';">Clear</button>
                                            <?PHP
                                        }
                                        else
                                            {
                                                ?>
                                                <input type="submit" class="btn btn-info" value="Submit">
                                                <?PHP
                                            }
                                        ?>
                                    </div>
                                <?PHP
                                if($is_filtering == false)
                                {
                                    ?>
                                        </form>
                                    <?PHP
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <?PHP
                                    foreach($Columns as $column)
                                    {
                                        HTML::print("<th>", false);
                                        HTML::print($column);
                                        HTML::print("</th>", false);
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?PHP
                                    foreach($Results as $result)
                                    {
                                        HTML::print("<tr>", false);
                                        foreach($result as $item => $value)
                                        {
                                            HTML::print("<td>", false);
                                            HTML::print($value);
                                            HTML::print("</td>", false);
                                        }
                                        HTML::print("</tr>", false);
                                    }
                                ?>
                            </tbody>
                        </table>

                    </div>

                    <nav>
                        <ul class="pagination">
                            <li class="page-item">
                                <?PHP
                                    $PreviousPage = ($CurrentPage - 1);
                                    if($PreviousPage < 1)
                                    {
                                        $PreviousPage = 1;
                                    }
                                    $Parameters = $_GET;
                                    $Parameters["page"] = $PreviousPage;
                                ?>
                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>">Previous</a>
                            </li>
                            <?PHP
                                $CurrentCounter = 1;
                                while(true)
                                {
                                    ?>
                                    <li class="page-item">
                                        <?PHP
                                            $Parameters = $_GET;
                                            $Parameters["page"] = $CurrentCounter;
                                        ?>
                                        <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                    </li>
                                    <?PHP

                                    $CurrentCounter += 1;

                                    if($CurrentCounter > $Pages)
                                    {
                                        break;
                                    }
                                }
                            ?>
                            <li class="page-item">
                                <?PHP
                                    $NextPage = ($CurrentPage + 1);
                                    if($NextPage > $Pages)
                                    {
                                        $NextPage = $Pages;
                                    }
                                    $Parameters = $_GET;
                                    $Parameters["page"] = $NextPage;
                                ?>
                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </main>
            </div>
        </div>
        <?PHP HTML::importSection("js_scripts"); ?>
        <?PHP Javascript::importScript('rpage'); ?>
        <script>
            $(".pagination").rPage();
        </script>
    </body>
</html>
