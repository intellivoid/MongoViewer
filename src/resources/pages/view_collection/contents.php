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
    $filter_value = null;
    $filter_error = false;
    if(isset($_GET["filter_by"]))
    {
        if(isset($_GET["filter_value"]))
        {
            if(is_array($_GET["filter_by"]) && is_array($_GET["filter_value"])) {
                if(count($_GET["filter_by"]) == count($_GET["filter_value"])) {
                    $filter = array_combine($_GET["filter_by"], $_GET["filter_value"]);
                    $is_filtering = true;
                } else {
                    $is_filtering = true;
                    $filter_error = true;
                }
            } else {
                $filter_value = $_GET["filter_value"];
                $filter = [$_GET["filter_by"] => $_GET["filter_value"]];
                $is_filtering = true;
            }
        }
    }

    // Check if we should render objects to the table or keep them in JSON (default: render)
    $renderObjectsToTable = true;
    if(isset($_GET["r_obj_to_table"]))  {
        if($_GET["r_obj_to_table"] == "0") {
            $renderObjectsToTable = false;
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
    $SkipContents = 0;
    $CurrentPage = 1;
    $TotalDocuments = $Collection->countDocuments(); // How many documents we are dealing with
    $TotalFilteredDocuments = $Collection->countDocuments($filter);
    if($is_filtering) {
        if($TotalFilteredDocuments > 0)
        {
            // Do maths!!!
            $Pages = (($TotalFilteredDocuments - 1) / $RecordsPerPage) + 1;

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
        }
    } else {
        if($TotalDocuments > 0)
        {
            // Do maths!!!
            $Pages = (($TotalDocuments - 1) / $RecordsPerPage) + 1;

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
        }
    }

    $options = ["limit" => $RecordsPerPage, "skip" => $SkipContents];
    if($_GET["col"] != "geotargets" && isset($_GET["sort"]) && $_GET["sort"] == "desc") {
	$options['sort'] = ["ts" => -1];
    }


    // Get the contents of the collection
    $Cursor = $Collection->find($filter, $options);
    $Columns = [];
    $RealColumns = [];
    $Results = [];

    /** @var BSONDocument $value */
    foreach($Cursor as $BSONDocument)
    {
        $ResultArray = array();
        foreach($Columns as $column)
        {
            $ResultArray[$column] = "NA";
        }

        foreach($BSONDocument as $item => $value)
        {
            $valIsObject = false;
            if(!in_array($item, ["_id", "ts"])) {
                if($renderObjectsToTable && gettype($value) === "object" && get_class($value) == "MongoDB\Model\BSONDocument") {
                    foreach ($value as $k => $v) {
                        if (in_array($k, $Columns) == false) {
                            $Columns[] = $k;
                            $RealColumns[] = $item.".".$k;
                        }
                    }
                    $valIsObject = true;
                } else {
                    if (in_array($item, $Columns) == false) {
                        $Columns[] = $item;
                        $RealColumns[] = $item;
                    }
                }


                $Value = $value;

                switch (gettype($value)) {
                    case "string":
                    case "integer":
                        $ResultArray[$item] = $value;
                        break;

                    case "object":
                        switch (get_class($value)) {
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
                                if($valIsObject && $renderObjectsToTable) {
                                    foreach ($BSONDocument as $BSONKey => $BSONValue) {
                                        $ResultArray[$BSONKey] = $BSONValue;
                                    }
                                } else {
                                    $ResultArray[$item] = json_encode($BSONDocument->jsonSerialize(), JSON_PRETTY_PRINT);
                                }

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
        }

        $Results[] = $ResultArray;
    }

    // Fix for a retarded motherfucking ridiculous development stupidity Vol. II
    // sorry for the bad words, but this triggered me kthx
    //
    // Changes: added edgy weird for loop.
    /*
     * This fixes the next problem:
     *  sample case:
     *   doc1 has 14 values (missing referer) (current cols are: gclid, random, rd, d, rl, cfc, loc, ip, ua, qs, rs, rd, date, ts). (THIS IS WHY THE STUPID REFERER JUST DOESN'T HAVE ANY VALUE BECAUSE THE KEY DOES NOT EXIST AT THIS MOMENT)
     *   doc2 has 15 values (has referer) (current cols are: gclid, random, rd, d, rl, cfc, loc, ip, ua, referer, qs, rs, rd, date, ts) (HERE THE KEY IS ADDED SINCE THERE IS A FUCKING REFERER)
     *   doc3 has 14 values (missing referer) (current cols are: gclid, random, rd, d, rl, cfc, loc, ip, ua, referer, qs, rs, rd, date, ts) (HERE REFERER IS NA, BECAUSE THE KEY FINALLY FUCKING EXISTS ON THE COLUMNS, EVEN IF THE DOCUMENT DOESN'T HAVE REFERER)
     */
    if(count($Results) > 0) {
        // Get the amount of items of the last Result
        $lastResultVC = count($Results[count($Results)-1]);

        // DO THIS EDGY STUPID HACK TO SHOW NA PROPERLY BECAUSE WE DO NOT KNOW WHAT CAN HAPPEN //
        // EXPENSIVE IF YOU ARE DEALING WITH RETARDED DATA //
        for($i = 0; $i < count($Results); $i++) {
            if (count($Results[$i]) != $lastResultVC) {
                $addedResults = 0;

                // # of Results that are missing on the Result that are on the last Result
                $missingResults = ($lastResultVC - count($Results[$i])); // delta results

                while ($addedResults < $missingResults) {
                    // Add NA to the Result Array until the number of results match with the last
                    $Results[$i][] = "NA";
                    $addedResults++;
                }
            } else {
                break;
            }
        }
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
                        <h3 class="" style="font-weight: 200;"><?PHP HTML::print($_GET["db"] . "." . $_GET["col"]); ?></h3>
                    </div>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="browse-tab" data-toggle="tab" href="#browse" role="tab" aria-controls="browse" aria-selected="true" style="font-weight: 600;"><i class="fa fa-files-o"></i>&nbsp;BROWSE</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="search-tab" data-toggle="tab" href="#search" role="tab" aria-controls="profile" aria-selected="false" style="font-weight: 600;"><i class="fa fa-search"></i>&nbsp;SEARCH</a>
                        </li>
                        <li class="nav-item" disabled hidden role="presentation">
                            <a class="nav-link" id="advanced-tab" data-toggle="tab" href="#advanced" role="tab" aria-controls="profile" aria-selected="false" style="font-weight: 600;"><i class="fa fa-object-group"></i>&nbsp;ADVANCED</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="cContextPCallerSecMongoViewer">
                        <div class="tab-pane fade show active" id="browse" role="tabpanel" aria-labelledby="browse-tab">
                            <br>
                            <div class="container-fluid">
                                <?PHP
                                if(($is_filtering && $TotalFilteredDocuments == 0) || ($TotalDocuments == 0)) {
                                ?>
                                    <div class="alert alert-warning" role="alert">
                                        <i class="fa fa-warning"></i>&nbsp;&nbsp;No documents were found on the collection<?= $is_filtering ? " with the specified criteria." : "."?>
                                    </div>
                                <?PHP
                                } else if($is_filtering && $filter_error) {
                                    ?>
                                    <div class="alert alert-danger" role="alert">
                                        <i class="fa fa-times"></i>&nbsp;&nbsp;Filter mismatch. Key->Value's are not pairs!.
                                    </div>
                                    <?PHP
                                } else {
                                ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="fa fa-check"></i>&nbsp;&nbsp;Showing documents <?=$SkipContents?> - <?=($SkipContents+count($Results))?> (<?=($is_filtering) ? $TotalFilteredDocuments." total filtered," : "$TotalDocuments total," ?> <?=$Collection->estimatedDocumentCount()?> estimated unfiltered)
                                </div>
                                <div class="row">
                                    <div class="nav-scroller py-1 mb-2 col-10 ">
                                        <nav class="nav d-flex">
                                            <ul class="pagination pagination-sm flex-wrap">
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
                                                    <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>">
                                                        <span aria-hidden="true">&laquo;</span>
                                                        <span class="sr-only">Previous</span>
                                                    </a>
                                                </li>
                                                <?PHP
                                                $CurrentCounter = 1;
                                                $EllipsisSet = false;
                                                while(true)
                                                {
                                                    if($Pages > 100) {
                                                        if(($CurrentCounter >= (($CurrentPage-5) < 0 ? 0 : ($CurrentPage-5))) && ($CurrentCounter <= ($CurrentPage+5)) && !($CurrentCounter >= ($Pages-5))) {
                                                            if($CurrentCounter == $CurrentPage) {
                                                                ?>
                                                                <li class="page-item active">
                                                                    <?PHP
                                                                    $Parameters = $_GET;
                                                                    $Parameters["page"] = $CurrentCounter;
                                                                    ?>
                                                                    <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                                </li>
                                                                <?PHP
                                                            } else {
                                                                ?>
                                                                <li class="page-item">
                                                                    <?PHP
                                                                    $Parameters = $_GET;
                                                                    $Parameters["page"] = $CurrentCounter;
                                                                    ?>
                                                                    <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                                </li>
                                                                <?PHP
                                                            }
                                                        }

                                                        if($CurrentCounter >= ($Pages-5)) {
                                                            if(!$EllipsisSet){
                                                                $EllipsisSet = true;
                                                                ?>
                                                                <li class="page-item disabled">
                                                                    <a class="page-link">...</a>
                                                                </li>
                                                                <?PHP
                                                            }
                                                            if($CurrentCounter == $CurrentPage) {
                                                                ?>
                                                                <li class="page-item active">
                                                                    <?PHP
                                                                    $Parameters = $_GET;
                                                                    $Parameters["page"] = $CurrentCounter;
                                                                    ?>
                                                                    <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                                </li>
                                                                <?PHP
                                                            } else {
                                                                ?>
                                                                <li class="page-item">
                                                                    <?PHP
                                                                    $Parameters = $_GET;
                                                                    $Parameters["page"] = $CurrentCounter;
                                                                    ?>
                                                                    <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                                </li>
                                                                <?PHP
                                                            }
                                                        }
                                                    } else {
                                                        if($CurrentCounter == $CurrentPage) {
                                                            ?>
                                                            <li class="page-item active">
                                                                <?PHP
                                                                $Parameters = $_GET;
                                                                $Parameters["page"] = $CurrentCounter;
                                                                ?>
                                                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                            </li>
                                                            <?PHP
                                                        } else {
                                                            ?>
                                                            <li class="page-item">
                                                                <?PHP
                                                                $Parameters = $_GET;
                                                                $Parameters["page"] = $CurrentCounter;
                                                                ?>
                                                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                            </li>
                                                            <?PHP
                                                        }
                                                    }

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
                                                    <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>">
                                                        <span aria-hidden="true">&raquo;</span>
                                                        <span class="sr-only">Next</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>

                                    </div>
                                    <div class="float-right form-control form-sm form-inline" style="padding: 0;border: 0;width:auto">
                                        <label for="sort">Sort by: &nbsp;</label>
                                        <select id="sort" class="form-control-sm" style="border: 1px lightgray solid;">
                                            <option value="asc">Ascending</option>
                                            <option <?=((isset($_GET["sort"]) && $_GET["sort"] == "desc")) ? "selected" : ""?> value="desc">Descending</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="table-responsive">
                                    <table class="table table-striped table-ellipsis table-sm">
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
                                <div class="nav-scroller py-1 mb-2">
                                    <nav class="nav d-flex">
                                        <ul class="pagination pagination-sm flex-wrap">
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
                                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>">
                                                    <span aria-hidden="true">&laquo;</span>
                                                    <span class="sr-only">Previous</span>
                                                </a>
                                            </li>
                                            <?PHP
                                            $CurrentCounter = 1;
                                            $EllipsisSet = false;
                                            while(true)
                                            {
                                                if($Pages > 100) {
                                                    if(($CurrentCounter >= (($CurrentPage-5) < 0 ? 0 : ($CurrentPage-5))) && ($CurrentCounter <= ($CurrentPage+5)) && !($CurrentCounter >= ($Pages-5))) {
                                                        if($CurrentCounter == $CurrentPage) {
                                                            ?>
                                                            <li class="page-item active">
                                                                <?PHP
                                                                $Parameters = $_GET;
                                                                $Parameters["page"] = $CurrentCounter;
                                                                ?>
                                                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                            </li>
                                                            <?PHP
                                                        } else {
                                                            ?>
                                                            <li class="page-item">
                                                                <?PHP
                                                                $Parameters = $_GET;
                                                                $Parameters["page"] = $CurrentCounter;
                                                                ?>
                                                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                            </li>
                                                            <?PHP
                                                        }
                                                    }

                                                    if($CurrentCounter >= ($Pages-5)) {
                                                        if(!$EllipsisSet){
                                                            $EllipsisSet = true;
                                                            ?>
                                                            <li class="page-item disabled">
                                                                <a class="page-link">...</a>
                                                            </li>
                                                            <?PHP
                                                        }
                                                        if($CurrentCounter == $CurrentPage) {
                                                            ?>
                                                            <li class="page-item active">
                                                                <?PHP
                                                                $Parameters = $_GET;
                                                                $Parameters["page"] = $CurrentCounter;
                                                                ?>
                                                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                            </li>
                                                            <?PHP
                                                        } else {
                                                            ?>
                                                            <li class="page-item">
                                                                <?PHP
                                                                $Parameters = $_GET;
                                                                $Parameters["page"] = $CurrentCounter;
                                                                ?>
                                                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                            </li>
                                                            <?PHP
                                                        }
                                                    }
                                                } else {
                                                    if($CurrentCounter == $CurrentPage) {
                                                        ?>
                                                        <li class="page-item active">
                                                            <?PHP
                                                            $Parameters = $_GET;
                                                            $Parameters["page"] = $CurrentCounter;
                                                            ?>
                                                            <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                        </li>
                                                        <?PHP
                                                    } else {
                                                        ?>
                                                        <li class="page-item">
                                                            <?PHP
                                                            $Parameters = $_GET;
                                                            $Parameters["page"] = $CurrentCounter;
                                                            ?>
                                                            <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>"><?PHP HTML::print($CurrentCounter); ?></a>
                                                        </li>
                                                        <?PHP
                                                    }
                                                }

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
                                                <a class="page-link" href="<?PHP DynamicalWeb::getRoute("view_collection", $Parameters, true); ?>">
                                                    <span aria-hidden="true">&raquo;</span>
                                                    <span class="sr-only">Next</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                                <?PHP
                                }
                                ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="search" role="tabpanel" aria-labelledby="search-tab">
                            <br>
                            <div class="container-fluid">
                                <?PHP
                                if($is_filtering == false)
                                {
                                ?>
                                <form method="GET" action="<?PHP DynamicalWeb::getRoute("view_collection", array("db" => $_GET["db"], "col" => $_GET["col"]), true); ?>">
                                <?PHP
                                }
                                ?>
                                <?PHP
                                if(count($RealColumns) == 0)
                                {
                                ?>
                                <select hidden disabled class="form-control" id="filter_by_base" name="filter_by_base[]">
                                    <option>No Values</option>
                                </select>
                                <?PHP
                                }
                                else
                                {
                                ?>
                                <select hidden disabled class="form-control" id="filter_by_base" name="filter_by_base[]">
                                    <?PHP
                                    foreach($RealColumns as $column)
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
                                <div class="table-responsive">
                                    <div class="table-wrapper">
                                        <div class="table-title">
                                            <div class="row">
                                                <div class="col"><h2>Search Filters</h2></div>
                                                <div class="col">
                                                    <button type="button" class="btn btn-info add-new float-right" disabled="disabled"><i class="fa fa-plus"></i> ADD NEW FILTER</button>
                                                </div>
                                            </div>
                                        </div>
                                        <table id="filterTable" class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Filter By</th>
                                                    <th>Filter Value</th>
                                                    <th style="width: 8%"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <?PHP
                                                        if(count($RealColumns) == 0)
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
                                                                foreach($RealColumns as $column)
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
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" value="">
                                                    </td>
                                                    <td style="text-align: center">
                                                        <a class="add" title="" data-toggle="tooltip" data-original-title="Add" style="display: inline;"><i class="material-icons"></i></a>
                                                        <a class="edit" title="" data-toggle="tooltip" data-original-title="Edit" style="display: none;"><i class="material-icons"></i></a>
                                                        <a class="delete" title="" data-toggle="tooltip" data-original-title="Delete"><i class="material-icons"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <input type="text" hidden="hidden" class="form-control" id="db" name="db" value="<?PHP HTML::print($_GET["db"]); ?>">
                                        <input type="text" hidden="hidden" class="form-control" id="col" name="col" value="<?PHP HTML::print($_GET["col"]); ?>">
                                        <input type="text" hidden="hidden" class="form-control" id="limit" name="limit" value="<?PHP HTML::print($RecordsPerPage); ?>">
                                        <?PHP
                                        if($is_filtering)
                                        {
                                            $Parameters = $_GET;
                                            unset($Parameters["filter_value"]);
                                            unset($Parameters["filter_by"]);
                                            ?>
                                            <input type="submit" class="btn btn-info" value="Submit">&nbsp;&nbsp;
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
                        <div class="tab-pane fade" id="advanced" role="tabpanel" aria-labelledby="advanced-tab">
                            <br>
                            <div class="container-fluid">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Render BSONDocuments into tables (Render Objects to Tables)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <?PHP HTML::importSection("js_scripts"); ?>
        <?PHP Javascript::importScript("app"); ?>
        <?PHP Javascript::importScript("collection"); ?>
        <?PHP Javascript::importScript("tdactive"); ?>
    </body>
</html>
