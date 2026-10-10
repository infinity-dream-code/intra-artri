<?php

require "include/jwt.php";
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
function parseHeaders( $headers )
{
    $head = array();
    foreach( $headers as $k=>$v )
    {
        $t = explode( ':', $v, 2 );
        if( isset( $t[1] ) )
            $head[ trim($t[0]) ] = trim( $t[1] );
        else
        {
            $head[] = $v;
            if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
                $head['reponse_code'] = intval($out[1]);
        }
    }
    return $head;
}

//=========================================
//class JWT
$transaksi = new JWT();
//SELECT MD5('FarrelGantengSekali')
$key = "27576a43cc88a0abbc5a6a509b51be9c";
$ResultArray = array();
$ResultArrayChild = array();
$decoded = $transaksi->decode($_GET['token'], $key, array('HS256'));

$decoded_array = (array) $decoded;

$METHOD = $decoded_array['METHOD'];
$USERNAME = $decoded_array['USERNAME'];
$PASSWORD = $decoded_array['PASSWORD'];

if (!$_GET['token'] || !isset($_GET['token'])) {
    $datas = array(
        'KodeRespon' => 90,
        'PesanRespon' => 'Token Tidak Terdefinisi'
    );
    // echo no users JSON
    echo json_encode($datas);
} else {
    if (isset($METHOD)) {
        switch ($METHOD) {
            case 'LoginRequest' :
                // include db connect class
                require_once "include/config_39MY_MOBILE.php";
                //masukkan NIM/NIS dan pass saja, untuk akses di CUST dikasih 
                $PASSWORD = MD5($PASSWORD);
                $sql = "SELECT COUNT(idincrement) as Exist, nama as Username FROM konsumsi_presensi_user
                    WHERE username='$USERNAME' AND Password='$PASSWORD'
                    ";
                $requery = mysqli_query($dbhandle,$sql);
                $row = mysqli_fetch_array($requery,MYSQLI_BOTH);
                $isExist = $row['Exist'];
                if( $isExist == '1' )
                {
                        $datas = array(
                            'Username' => $row['Username'],
                            'KodeRespon' => 1
                        );
                        // echo no users JSON
                        echo json_encode($datas);
                }else{
                        $datas = array(
                        'KodeRespon' => 10,
                        'PesanRespon' => 'Akses Ditolak'
                        );
                        // echo no users JSON
                        echo json_encode($datas);
                }
      
                break;
                case 'POSTKonsumsi' :
                    // include db connect class
                    require_once "include/config_39MY_MOBILE.php";
    
                        $PID =  $decoded_array['NOKARTU'];
                        $Username =  $decoded_array['USERNAME'];
                
                        
                        $sql = "SELECT AndroidKonsumsiPresensi('$PID','$Username') as Result";
                        $requery = mysqli_query($dbhandle,$sql);
                        $row = mysqli_fetch_array($requery,MYSQLI_BOTH);
                        $isResult = $row['Result'];
                        $isResult = explode("|",$isResult);
                        $response = array();	
                        $CHECK = $isResult[0];
                        if($CHECK == 'SERVICE_HAD_PROVIDED'){
                            $datas = array(
                                'STATUS' => 'NOTOK',
                                'RESULT' => 'TIDAK_MEMILIKI_KONSUMSI_TERSISA',
                                'RES' => 'KONSUMSI TELAH DIAMBIL',	
                                                        
                            );
                        }elseif($CHECK == 'UNKNOWN_OR_BLOCKED_CARD'){
                            $datas = array(
                                'STATUS' => 'NOTOK',
                                'RESULT' => 'KARTU_TIDAK_TERDAFTAR',
                                'RES' => 'QR SALAH',	
                                                    
                            );
                        }elseif($CHECK == 'NOT_SERVICE_TIME'){
                            $datas = array(
                                'STATUS' => 'NOTOK',
                                'RESULT' => 'BUKAN_WAKTU_MAKAN',
                                'RES' => 'DILUAR WAKTU',	
                                                    
                            );
                        }elseif($CHECK == 'OK'){
                            $datas = array(
                                'STATUS' => 'OK',
                                'NAMA' => $isResult[1],	
                                'RES' => strval($isResult[2]),						
                            );
    
                        }else{
                            $datas = array(
                                'STATUS' => 'NOTOK',
                                'RESULT' => 'Koneksi_Error',
                                'RES' => '-',	
                                                    
                            );
                        }
                        
                        
                        array_push($response, $datas);
                        echo json_encode($response);
    
                break;
            
            // case 'LogTransaksiRequest' :
                
            //     // include db connect class
            //     require_once "include/config_39MY_MOBILE.php";
                
            //     $USERNAME = $decoded_array['USERNAME'];
            //     $sql = "SELECT `User`,NMCUST,TRXDATE,CODE02,
            //     IFNULL(DATE_FOOD1,'-') AS DATE_FOOD1 ,IFNULL(DATE_FOOD2,'-') AS DATE_FOOD2 ,IFNULL(DATE_FOOD3,'-') AS DATE_FOOD3 ,
            //     if(isFOOD1 = '1' , 'HADIR' , 'TIDAK HADIR') as isFOOD1 ,
            //     if(isFOOD2 = '1' , 'HADIR' , 'TIDAK HADIR') as isFOOD2 ,
            //     if(isFOOD3 = '1' , 'HADIR' , 'TIDAK HADIR') as isFOOD3 
            //      FROM Konsumsi_presensi_siswa WHERE `User` ='$USERNAME' ORDER BY idincrement DESC LIMIT 100 ";
            //     $requery = mysqli_query($dbhandle,$sql);
            //     $response["datas"] = array();

            //     while ($row = mysqli_fetch_array($requery,MYSQLI_BOTH)){
            //         $datas = array(
            //             'NamaCust' => $row['NMCUST'],
            //             'TRXDATE' => strval($row['TRXDATE']),
            //             'KANTIN' => $row['User'],
            //             'UNIT' => $row['CODE02'],
            //             'JAM_1' => strval($row['DATE_FOOD1']),
            //             'JAM_2' => strval($row['DATE_FOOD2']),
            //             'JAM_3' => strval($row['DATE_FOOD3']),
            //             'JADWAL_1' => $row['isFOOD1'],
            //             'JADWAL_2' => $row['isFOOD2'],
            //             'JADWAL_3' => $row['isFOOD3'],
                        
                        
            //         );
            //         array_push($response["datas"], $datas);
            //     }

            //     echo json_encode($response);
                

            // break;
            case 'LogTransaksiRequest' :
                
                // include db connect class
                require_once "include/config_39MY_MOBILE.php";
                
                $USERNAME = $decoded_array['USERNAME'];
                $sql = "SELECT `User`,NMCUST,TRXDATE,CODE02,
                IFNULL(DATE_FOOD1,'-') AS DATE_FOOD1 ,IFNULL(DATE_FOOD2,'-') AS DATE_FOOD2 ,IFNULL(DATE_FOOD3,'-') AS DATE_FOOD3 ,IFNULL(DATE_FOOD4,'-') AS DATE_FOOD4 ,
                if(isFOOD1 = '1' , 'HADIR' , 'TIDAK HADIR') as isFOOD1 ,
                if(isFOOD2 = '1' , 'HADIR' , 'TIDAK HADIR') as isFOOD2 ,
                if(isFOOD3 = '1' , 'HADIR' , 'TIDAK HADIR') as isFOOD3 ,
                if(isFOOD4 = '1' , 'HADIR' , 'TIDAK HADIR') as isFOOD4 
                 FROM Konsumsi_presensi_siswa WHERE `User` ='$USERNAME' ORDER BY idincrement DESC LIMIT 100 ";
                $requery = mysqli_query($dbhandle,$sql);
                $response["datas"] = array();

                while ($row = mysqli_fetch_array($requery,MYSQLI_BOTH)){
                    $datas = array(
                        'NamaCust' => $row['NMCUST'],
                        'TRXDATE' => strval($row['TRXDATE']),
                        'KANTIN' => $row['User'],
                        'UNIT' => $row['CODE02'],
                        'JAM_1' => strval($row['DATE_FOOD1']),
                        'JAM_2' => strval($row['DATE_FOOD2']),
                        'JAM_3' => strval($row['DATE_FOOD3']),
                        'JAM_4' => strval($row['DATE_FOOD4']),
                        'JADWAL_1' => $row['isFOOD1'],
                        'JADWAL_2' => $row['isFOOD2'],
                        'JADWAL_3' => $row['isFOOD3'],
                        'JADWAL_4' => $row['isFOOD4'],
                        
                        
                    );
                    array_push($response["datas"], $datas);
                }

                echo json_encode($response);
                

            break;
            case 'RequestNewPassword' :
                // penting
                $response["datas"] = array();
                $PASSWORD = $decoded_array['PASSWORD'];
                $NEWPASSWORD = $decoded_array['NEWPASSWORD'];
                $NEWPASSWORD2 = $decoded_array['NEWPASSWORD2'];

                require_once "include/config_39MY_MOBILE.php";
                if ($NEWPASSWORD == $NEWPASSWORD2) {
                    #$PASSWORD = MD5($PASSWORD);
                    #$NEWPASSWORD = MD5($NEWPASSWORD);
                    //$NEWPASSWORD2 = SHA1($NEWPASSWORD2);

                    $requery = mysqli_query($dbhandle,"CALL AndroidChangePassKonsumsi('$USERNAME', '$PASSWORD', '$NEWPASSWORD')");
                    $datas = array(
                        'KodeRespon' => 1,
                        'PesanRespon' => 'SUKSES GANTI PASSWORD'
                    );
                    // echo no users JSON
                    echo json_encode($datas);
                } else {
                    $datas = array(
                        'KodeRespon' => 0,
                        'PesanRespon' => 'PASSWORD KONFIRMASI TIDAK COCOK'
                    );
                    // echo no users JSON
                    echo json_encode($datas);
                }

                break;
        }
    } else {
        $datas = array(
            'KodeRespon' => 91,
            'PesanRespon' => 'Metode Request Tidak Benar'
        );
        // echo no users JSON
        echo json_encode($datas);
    }
}