<?php 
include("bdd.php");
header("Access-Control-Allow-Origin: *");

$localhost="https://localhost/";

/********Recuperer les categories*********************************/


switch($_GET['actions'])
{
    case 'getcategories':

$query="SELECT * FROM categorie WHERE afficher_categorie=1"; 
$result=$db->query($query); 
// db est une variable qui contient ma base de données
$res['categorie']=[];

 while($cat=$result->fetch_assoc())
 {
     $res['categorie'][]=$cat;
 }
 echo json_encode($res);
 break;




 /********Recuperer les produits Categorie =portable */

case 'getCatPortable':
 
 $query="SELECT * FROM produit WHERE id_scategorie IN (SELECT id_scategorie FROM scategorie WHERE id_categorie =12 )AND afficher=1"; 
 $result=$db->query($query); 
 $res['produit']=[];

  if($result)
  { 
        while($cat=$result->fetch_assoc())
    {
      $cat['image_min']=$localhost.$cat['image_min'];
      $res['produit'][]=$cat;
    }
    echo json_encode($res);
}  
break;
//**************** Recuperer les produits en promo***************** 

case 'getpromoproduct':
 $query="SELECT * FROM produit WHERE promo=1 AND afficher=1 AND id_scategorie IN(SELECT id_scategorie FROM scategorie WHERE afficher_scategorie =1 AND id_categorie IN(SELECT id_categorie FROM categorie WHERE afficher_categorie =1))"; 
$result=$db->query($query);  

 while($cat=$result->fetch_assoc())
 {
    $cat['image_min']=$localhost.$cat['image_min']; 
     $res['produit'][]=$cat;
 }
 echo json_encode($res);
 break;

//********************************************************************* 

//***************************Recuperer les pubs****************************/
case 'getpubs':
 $query="SELECT * FROM pubs "; 
$result=$db->query($query);  

 while($cat=$result->fetch_assoc())
 {
     $cat['image_pubs']=$localhost."images/pubs/".$cat['image_pubs'];
     $res['pubs'][]=$cat;
     
 }
 echo json_encode($res);
 break;
 /***************************************************************************/

 case 'getproducts':
 $query="SELECT * FROM produit WHERE afficher=1 "; 
$result=$db->query($query);  

 while($cat=$result->fetch_assoc())
 {
    $cat['image_min']=$localhost.$cat['image_min'];
     $res['produit'][]=$cat;
 }
 echo json_encode($res);
 break;
}
