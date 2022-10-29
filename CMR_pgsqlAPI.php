<?php
if (isset($_POST['functionname'])) {
    $paPDO = initDB();
    $paSRID = '4326';
    $paPoint = $_POST['paPoint'];
    $functionname = $_POST['functionname'];

    $aResult = "null";
    if ($functionname == 'getGeoCMRToAjax')
        $aResult = getGeoCMRToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getInfoCMRToAjax')
        $aResult = getInfoCMRToAjax($paPDO, $paSRID, $paPoint);

    echo $aResult;

    closeDB($paPDO);
}


function initDB()
{
    $host = 'localhost';
    $db = 'testCSDL';
    $user = 'postgres';
    $password = 'root';
    $post = '5433';
    // Kết nối CSDL

    
    // try {
        $dsn = "pgsql:host=$host;port=$post;dbname=$db;";
        $paPDO = new PDO($dsn, $user, $password);
        return $paPDO;
    // } catch (PDOException $e) {
    //     die($e->getMessage());
    // }
}
function query($paPDO, $paSQLStr)
{
    try {
        // Khai báo exception
        $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Sử đụng Prepare 
        $stmt = $paPDO->prepare($paSQLStr);
        // Thực thi câu truy vấn
        $stmt->execute();

        // Khai báo fetch kiểu mảng kết hợp
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        // Lấy danh sách kết quả
        $paResult = $stmt->fetchAll();
        return $paResult;
    } catch (PDOException $e) {
        echo "Thất bại, Lỗi: " . $e->getMessage();
        return null;
    }
}
function closeDB($paPDO)
{
    // Ngắt kết nối
    $paPDO = null;
}
function example1($paPDO)
{
    $mySQLStr = "SELECT * FROM \"khuvuchn\"";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            echo $item['name'] . ' - ' . $item['address'];
            echo "<br>";
        }
    } else {
        echo "example1 - null";
        echo "<br>";
    }
}
function example2($paPDO)
{
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"khuvuchn\"";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            echo $item['geo'];
            echo "<br><br>";
        }
    } else {
        echo "example2 - null";
        echo "<br>";
    }
}
function example3($paPDO, $paSRID, $paPoint)
{
    echo $paPoint;
    echo "<br>";
    $paPoint = str_replace(',', ' ', $paPoint);
    echo "<br>";
    //$mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm41_vnm_3\" where ST_Within('POINT(12 5)'::geometry,geom)";
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"khuvuchn\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    echo $mySQLStr;
    echo "<br><br>";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            echo $item['geo'];
            echo "<br><br>";
        }
    } else {
        echo "example2 - null";
        echo "<br>";
    }
}
function getResult($paPDO, $paSRID, $paPoint)
{
    //echo $paPoint;
    //echo "<br>";
    $paPoint = str_replace(',', ' ', $paPoint);
    //echo $paPoint;
    //echo "<br>";
    // $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm41_vnm_3\" where ST_Within('SRID=4326;POINT(12 5)'::geometry,geom)";
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"khuvuchn\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    //echo $mySQLStr;
    //echo "<br><br>";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geo'];
        }
    } else
        return "null";
}
function getGeoCMRToAjax($paPDO, $paSRID, $paPoint)
{

    $paPoint = str_replace(',', ' ', $paPoint);
    // $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"hnpark\" where ST_AsGeoJson(geom) = ('SRID=4326;". $paPoint ."'::geometry,geom)";
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"hnpark\"";
    //echo $mySQLStr;
    //echo "<br><br>";
    $result = query($paPDO, $mySQLStr);
    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geo'];
        }
    } else
        return "null";
}
function getInfoCMRToAjax($paPDO, $paSRID,$paPoint)
{   
    $mySQLStr = "SELECT  *
    from  \"hnpark\" 
    where ST_Distance('$paPoint', hnpark.geom) <= all(select ST_Distance('$paPoint', hnpark.geom) from \"hnpark\") 
    and ST_Distance('$paPoint', hnpark.geom) < 0.0025";
    $result = query($paPDO, $mySQLStr);
    if ($result != null) {
        $resFin = '<table>';
        // Lặp kết quả
        foreach ($result as $item) {
            // var_dump($item);
            $resFin = $resFin . '<tr><td>Công viên: ' . $item['name'] . '</td>
            <tr><td> Địa chỉ : '. $item['location'] .'</td></tr>
            </tr>';
        }
        $resFin = $resFin . '</table>';
        return $resFin;
    }
}

function getSearch($search)
{
    //echo $paPoint;
    //echo "<br>";
    $paPDO = initDB();
        $mySQLStr = "SELECT *
            from  \"hnpark\" 
            where lower(hnpark.location) like lower('%$search%')";
        //echo $mySQLStr;
        //echo "<br><br>";
        $result = query($paPDO, $mySQLStr);
        
    if ($result != null)
    {   
        $resFin = '
        <table class="table">
            <thead>
                <tr>
                <th scope="col">Công viên</th>
                <th scope="col">Địa Chỉ</th>
                </tr>
            </thead>
        <tbody>'; 
        
         foreach ($result as $value){
             $resFin = $resFin.'<tr>
             <td>'.$value['name'].'</td>';
             $resFin = $resFin.'<td>'.$value['location'].'</td>
             </tr>';
        //     $resFin = $resFin.'<br>'; 
         }
         $resFin = $resFin.'</tbody>
         </table>'; 
        
         echo $resFin;
    }
    else
        return "error";
}

