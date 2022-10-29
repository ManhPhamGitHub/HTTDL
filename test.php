<?php 
                $host = 'localhost';
                $db = 'testCSDL';
                $user = 'postgres';
                $password = 'root';
                $post = '5433';
                    $dsn = "pgsql:host=$host;port=$post;dbname=$db;";
                    $paPDO = new PDO($dsn, $user, $password);
                    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo , name , location from \"hnpark\" ";
                    $result = query($paPDO, $mySQLStr);
                    if ($result != null) {
                        $resFin = '<table>';
                        // Lặp kết quả
                        foreach ($result as $item) {
                            
                            $resFin = $resFin . '<tr><td>' . $item['name'] . '</td>
                            <td>'. $item['location'] .'</td>

                            <td><button onclick={TEST('. $item['geo'].')}>vcl luon</button></td>
                            </tr>';
                        }
                        $resFin = $resFin . '</table>';
                        echo $resFin;
                    }
            ?> 