<?php
header('Access-Control-Allow-Origin: *');
 header('Access-Control-Allow-Methods: GET, POST ,OPTIONS'); 
 header("Access-Control-Allow-Credentials:true ");
header("Access-Control-Allow-Headers: Content-Type,Authorization, x-Requested-with");
header("Content-Type: applicaion/json; charset=utf-8"); 

include "bdd.php";

$localhost="https://localhost/";

$postjson=json_decode(file_get_contents('php://input'),true); 

/***************************Inscription*************************************/

if ($postjson['actions']=="add_user")
 { 
  
       $password=md5($postjson['password']);
        $username=$postjson['username'];
        $email=$postjson['email'];
         $nom=$postjson['name'];
          $prenom=$postjson['firstName'];
          $num_fix=$postjson['telFixe'];
           $num_tel=$postjson['telMobile'];
           $adresse=$postjson['adresse'];
  

       $sql="SELECT * FROM client WHERE username='$username'";
       $query =mysqli_query($db,$sql);
       $check=mysqli_num_rows($query);
       if($check>0)
        {
          $result = json_encode(array ('success'=> false, 'msg' => 'Ce compte existe deja !'));
        }
      else
       {   
          if(!preg_match("#^[0]27|21|29|32|34|25|26|29|43|46|26|21|23|27|34|36|48|38|48|38|37|31|25|45|35|45|29|41|49|29|35|24|38|49|46|32|32|37|24|31|27|49|43|29|46[0-9]{5}[0-9]$#",$num_fix))
          
           {
             $result = json_encode(array ('success'=> false, 'msg' => 'Veuillez inserez un numero de téléhone fixe valide'));
          } 
          else 
          {
             if(!preg_match("#^[0][567][0-9]{7}[0-9]$#",$num_tel)) 
              { 
               $result = json_encode(array ('success'=> false, 'msg' => 'Veuillez inserez un numero de téléhone mobile valide'));
              }
             else
             {

               $sql1="INSERT INTO panier(username,etat_panier)values('$username',0)";
               $query1=mysqli_query($db,$sql1);
               $sql="INSERT INTO client (username,password1,email,nom,prenom,num_fix,num_tel,etat,adresse) values('$username','$password','$email','$nom','$prenom','$num_fix','$num_tel',1,'$adresse')";
               $query =mysqli_query($db,$sql);

               if($query) 
                 {
                  $datauser =array (
                     'nom'=> $nom,
                     'prenom'=>$prenom,
                     'email' =>$email,
                     'num_fix'=>$num_fix,
                     'num_tel'=>$num_tel,
                     'username'=>$username,
                     'password'=>$password,
                     'adresse'=>$adresse
                  );
	                 $result =json_encode(array('success' => true,'msg' => 'Inscription réussite !', 'result'=>$datauser));
                 }
              else 
                 {
	                $result = json_encode(array ('success'=> false, 'msg' => 'Inscription impossible, réesseyez'));
                 }	  
              }
      
          }
        }
    echo $result;
  }
  /***********************************Authentification*********************/
  if ($postjson['actions']=="logIn")
  {
    $password=md5($postjson['password']);
    $username=$postjson['username'];
    $sql="SELECT * FROM client WHERE username='$username' AND password1='$password'";
    $query=mysqli_query($db,$sql);
    $check=mysqli_num_rows($query);

    if($check>0)
    {
     $data=mysqli_fetch_array($query);
     $datauser =array (
       'nom'=> $data['nom'],
       'prenom'=>$data['prenom'],
       'email' =>$data['email'],
        'num_fix'=>$data['num_fix'],
        'num_tel'=>$data['num_tel'],
        'username'=>$data['username'],
        'password'=>$data['password1'],
        'adresse'=>$data['adresse']

     );


   if($query) 
  {
	  $result =json_encode(array('success' => true,'result'=>$datauser));
  }
  
  else 
  {
	  $result = json_encode(array ('success'=> false, 'msg' => 'Connexion impossible,réesseyez'));
  }	  
}
  else 
  {
     $result = json_encode(array ('success'=> false, 'msg' => 'Ce compte est inexistant !'));
  }
    
    echo $result;
  
}
/****************************Recuperer les produits par categories*********************************/
if ($postjson['actions']=="getPro")
{
  $idCat=$postjson['idCat'];
  $sql="SELECT * FROM produit WHERE id_scategorie IN (SELECT id_scategorie FROM scategorie WHERE id_categorie='$idCat') AND afficher=1";
  $result=$db->query($sql); 

  $check=mysqli_num_rows($result);

  $res['produit']=[];

  if($check >0)
  {     
   
        while($cat=$result->fetch_assoc())
    {
      
      $cat['image_min']=$localhost. $cat['image_min'];
      $res['produit'][]=$cat;
     
    }
    echo json_encode($res);
  } 

  else 
  {
   echo json_encode(null);
  }
   
  
}

/***********************************Recuperer les sous categories ********************************/

if ($postjson['actions']=="getSubCatFromCat")
{
  $idCat=$postjson['idCat'];
  $sql="SELECT * FROM scategorie WHERE id_categorie='$idCat'  AND afficher_scategorie=1";

  $result=$db->query($sql); 

  $check=mysqli_num_rows($result);

  $res['scategorie']=[];

  if($check >0)
  {     
   
     while($cat=$result->fetch_assoc())
    {   
      $res['scategorie'][]=$cat;
     
    }
    echo json_encode($res);
  } 

  else 
  {
   echo json_encode(null);
  }
   
  
}

/*************Recuperer les produits par sous categories**********************/
if ($postjson['actions']=="getSubPro")
{
  $idCat=$postjson['idCat'];
  $sql="SELECT * FROM produit WHERE id_scategorie='$idCat' AND afficher=1";
  $result=$db->query($sql); 

  $check=mysqli_num_rows($result);

  $res['produit']=[];

  if($check >0)
  {     
   
        while($cat=$result->fetch_assoc())
    {
      
      $cat['image_min']=$localhost. $cat['image_min'];
      $res['produit'][]=$cat;
     
    }
    echo json_encode($res);
  } 

  else 
  {
   echo json_encode(null);
  }
   
  

}
/**************************************************Recherche*******************************************************/
if ($postjson['actions']=="reserch")
{
  $term=$postjson['term'];
  $term=htmlspecialchars($term);
  $term=trim($term);
  $term=strip_tags($term);
  $term=ucwords($term);
  $term= addslashes($term);	
 if($term != "")
 {
  $sql="SELECT * FROM produit WHERE id_scategorie IN (SELECT id_scategorie FROM scategorie WHERE afficher_scategorie=1 AND  id_categorie IN(SELECT id_categorie FROM categorie WHERE afficher_categorie=1 AND nom_categorie LIKE '%$term%'))AND afficher=1 ";
  $result=$db->query($sql); 

  $check=mysqli_num_rows($result);

  $res['produit']=[];

  if($check >0)
      {     
        while($cat=$result->fetch_assoc())
         {
            $cat['image_min']=$localhost. $cat['image_min'];
            $res['produit'][]=$cat;
          }
          echo json_encode($res);
       } 

       else 
        {

         $sql="SELECT * FROM produit WHERE id_scategorie IN (SELECT id_scategorie FROM scategorie WHERE nom_scategorie LIKE '%$term%' AND afficher_scategorie=1 AND id_categorie IN(SELECT id_categorie FROM categorie WHERE afficher_categorie=1) )AND afficher=1 ";
         $result=$db->query($sql); 

         $check=mysqli_num_rows($result);

         $res['produit']=[];

         if($check >0)
             {     
               while($cat=$result->fetch_assoc())
                {
                   $cat['image_min']=$localhost. $cat['image_min'];
                   $res['produit'][]=$cat;
                 }
                 echo json_encode($res);
              } 
              else
              {

                $sql="SELECT * FROM produit WHERE id_marque IN(SELECT id_marque FROM marque WHERE marque LIKE '%$term%') AND afficher=1";
                $result=$db->query($sql); 
           
                $check=mysqli_num_rows($result);
           
                $res['produit']=[];
           
                if($check >0)
                     {     
                       while($cat=$result->fetch_assoc())
                        {
                         $cat['image_min']=$localhost. $cat['image_min'];
                        $res['produit'][]=$cat;
                        }
                       echo json_encode($res);
                     } 
                     else 
                        {
                          $sql="SELECT * FROM produit WHERE modele LIKE '%$term%' AND afficher=1 AND id_scategorie IN(SELECT id_scategorie FROM scategorie WHERE afficher_scategorie =1 AND id_categorie IN(SELECT id_categorie FROM categorie WHERE afficher_categorie =1))";
                          $result=$db->query($sql); 
                 
                          $check=mysqli_num_rows($result);
                 
                          $res['produit']=[];
                 
                          if($check >0)
                            {     
                               while($cat=$result->fetch_assoc())
                                 {
                                  $cat['image_min']=$localhost. $cat['image_min'];
                                 $res['produit'][]=$cat;
                                 }
                                echo json_encode($res);
                            } 
                        else 
                        {
                         echo json_encode(null);
                        }
                 }
            } 
       } 
   } 
   else  
   {
    echo json_encode(null);
   }
    
} 
/*****************************Recuperer les marques par categorie******************************* */
if ($postjson['actions']=="getCatMarks")
{
 
  $idCat=$postjson['idCat'];
  $sql="SELECT * FROM marque WHERE id_marque IN (SELECT id_marque FROM produit WHERE id_scategorie IN (SELECT id_scategorie FROM scategorie WHERE id_categorie ='$idCat'))";
  $result=$db->query($sql); 

  $check=mysqli_num_rows($result);

  $res['marque']=[];

  if($check >0)
  {     
    
     while($cat=$result->fetch_assoc())
      {
      $cat['logo']=$localhost. $cat['logo'];
      $res['marque'][]=$cat;
      }
      echo json_encode($res);
 } 
 else 
 {
    echo json_encode(null);
 }  
}
/***************************Recuperer les marques par sous categories******************************** */
if ($postjson['actions']=="getSubCatMarks")
{
 
  $idCat=$postjson['idCat'];
  $sql="SELECT * FROM marque WHERE id_marque IN (SELECT id_marque FROM produit WHERE id_scategorie ='$idCat')";
  $result=$db->query($sql); 

  $check=mysqli_num_rows($result);

  $res['marque']=[];

  if($check >0)
  {     
    
     while($cat=$result->fetch_assoc())
      {
      $cat['logo']=$localhost. $cat['logo'];
      $res['marque'][]=$cat;
      }
      echo json_encode($res);
 } 
 else 
 {
    echo json_encode(null);
 }  
}
/***************Recuperer les produits selon le filtre appliqué par categorie****************************** */
if ($postjson['actions']=="getProCatFiltre")
{
  $idCat=$postjson['idCat'];
  $lowerPrice=$postjson['lowerPrice'];
  $upperPrice=$postjson['upperPrice'];
  $listOfMarks=$postjson['listOfMarks'];
  $res['produit']=[];
  foreach($listOfMarks as $index=>$idmark)
  {
    $sql="SELECT * FROM produit WHERE id_scategorie IN (SELECT id_scategorie FROM scategorie WHERE id_categorie='$idCat')AND prix BETWEEN $lowerPrice AND $upperPrice AND id_marque= '$idmark' AND afficher=1";
    $result=$db->query($sql); 
    $check=mysqli_num_rows($result);

    if($check >0)
    {     
      while($cat=$result->fetch_assoc())
      {
        $cat['image_min']=$localhost. $cat['image_min'];
        $res['produit'][]=$cat;
      }
    }  
  } 
  echo json_encode($res);
}
/****************************Recuperer le filtre appliqué selon la sous categorie************************* */
if ($postjson['actions']=="getProSubCatFiltre")
{
  $idCat=$postjson['idCat'];
  $lowerPrice=$postjson['lowerPrice'];
  $upperPrice=$postjson['upperPrice'];
  $listOfMarks=$postjson['listOfMarks'];
  $res['produit']=[];
  foreach($listOfMarks as $index=>$idmark)
  {
    $sql="SELECT * FROM produit WHERE id_scategorie ='$idCat' AND prix BETWEEN $lowerPrice AND $upperPrice AND id_marque= '$idmark' AND afficher=1";
    $result=$db->query($sql); 
    $check=mysqli_num_rows($result);

    if($check >0)
    {     
      while($cat=$result->fetch_assoc())
      {
        $cat['image_min']=$localhost. $cat['image_min'];
        $res['produit'][]=$cat;
      }
    }  
  } 
  echo json_encode($res);
}
/***************************Changer MDP******************************** */
if ($postjson['actions']=="changePassword")
{
  $newPassword=md5($postjson['newPassword']);
  $username=$postjson['Username'];
  $sql="UPDATE client SET password1 = '$newPassword' WHERE username = '$username'";
  $query=mysqli_query($db,$sql);
 

 if($query) 
{
  $result =json_encode(array('success' => true,'msg'=>'Votre mot de passe a bien etait modifié'));
}
else 
{
  $result = json_encode(array ('success'=> false, 'msg' => 'Erreur, réesseyer '));
}	  

  
  echo $result;

}
/*******************************Changer autre info******************************************/
if ($postjson['actions']=="ChangeInfoProfil")
{
  $oldUsername=$postjson['OldUsername'];
  $username=$postjson['Username'];
  $name=$postjson['Name'];
  $FirstName=$postjson['FirstName'];
  $NumFix=$postjson['NumFix'];
  $NumMobile=$postjson['NumMobile'];
  $Email=$postjson['Email'];
  $Adresse=$postjson['Adresse'];
  $UsernameChanged=$postjson['Changed'];
  
  $sql="SELECT * FROM client WHERE username ='$username'";
  $result=$db->query($sql); 
  $check=mysqli_num_rows($result);
  
    if( $UsernameChanged ==true && $check >0)
      {
        $result = json_encode(array ('success'=> false, 'msg' => 'Ce compte existe deja'));
      }
    else
       {
         if(!preg_match("#^[0]27|21|29|32|34|25|26|29|43|46|26|21|23|27|34|36|48|38|48|38|37|31|25|45|35|45|29|41|49|29|35|24|38|49|46|32|32|37|24|31|27|49|43|29|46[0-9]{5}[0-9]$#",$NumFix))
           {
              $result = json_encode(array ('success'=> false, 'msg' => 'Veuillez inserez un numero de téléhone fixe valide'));
           } 
         else 
            {
              if(!preg_match("#^[0][567][0-9]{7}[0-9]$#",$NumMobile)) 
              { 
                $result = json_encode(array ('success'=> false, 'msg' => 'Veuillez inserez un numero de téléhone mobile valide'));
              }
              else 
              {
                 $sql="UPDATE client SET username='$username',nom='$name',prenom='$FirstName',email='$Email', num_tel='$NumMobile', num_fix='$NumFix', adresse='$Adresse' WHERE username = '$oldUsername'";
                 $query=mysqli_query($db,$sql);

                 if($query) 
                   {
                  

                    $result =json_encode(array('success' => true,'msg'=>'Votre profile a bien etait mis à jour'));
                   }
                   else 
                    {
                      $result = json_encode(array ('success'=> false, 'msg' => 'Erreur, réesseyer '));
                    }
                 } 	   
                } 
              }            
  echo $result;
              
}
/************************************************Recuperer client******************************************************/
if ($postjson['actions']=="getUser")
 { 
  
        $username=$postjson['username'];

       $sql="SELECT * FROM client WHERE username='$username'";
       $query =mysqli_query($db,$sql);
       $check=mysqli_num_rows($query);
       $res['client']=[];

       if($check>0)
       {     
          $data=mysqli_fetch_array($query);
           $datauser =array (
           'nom'=> $data['nom'],
           'prenom'=>$data['prenom'],
           'email' =>$data['email'],
           'num_fix'=>$data['num_fix'],
           'num_tel'=>$data['num_tel'],
           'username'=>$data['username'],
           'password'=>$data['password1'],
           'adresse'=>$data['adresse']
        );
        echo json_encode($datauser);

         } 
      else 
       
       echo json_encode(null);
 } 
 /**********************************************Ajouter au panier****************************************** */

 if ($postjson['actions']=="add_cart")
 { 
  
        $username=$postjson['username'];
        $id_produit=$postjson['id_produit'];
        $qtt_panier=$postjson['qtt_panier'];
        $prix=$postjson['prix'];
         

       $sql1="UPDATE panier SET etat_panier=1 ,total=total+ ('$prix' * '$qtt_panier') WHERE username='$username'";
       $query1=mysqli_query($db,$sql1);
        
       $sql="INSERT INTO panier_details(username,id_produit, qte) VALUES('$username','$id_produit','$qtt_panier')";

       $query =mysqli_query($db,$sql);
       
       if($query)
       {
         $result=json_encode(array('success' => true,'msg'=>'Votre panier à bien etait mise à jour'));
       }
       else
       {
        $result=null;
       }
       echo json_encode($result);
      }
      /******************************************Récuperer les  id produits du panier************************************************* */
      if($postjson['actions']=="getCart")
      {
        $username=$postjson['username'];
        $sql="SELECT id_produit FROM panier_details WHERE username='$username'";
        $result=$db->query($sql); 
      
        $check=mysqli_num_rows($result);
      
        $res['id_produit']=[];
      
        if($check >0)
        {     
         
              while($cat=$result->fetch_assoc())
          {
            
            $res['id_produit'][]=$cat;
           
          }
          echo json_encode($res);
        } 
      
        else 
        {
         echo json_encode(null);
        }
      }      
      /***********************Ajouter quantité produit********************************** */
      if($postjson['actions']=="updateCartAdd")
      { 
        $id_produit=$postjson['id_produit'];
        $username=$postjson['username'];
        $prix=$postjson['prix'];

        $sql1="UPDATE panier SET total=total+'$prix' WHERE username='$username'";
        $query1=mysqli_query($db,$sql1);
        $sql="UPDATE panier_details SET qte= qte+1 WHERE username='$username' AND id_produit='$id_produit'";
        $query=mysqli_query($db,$sql);
      
        if($query)
        {    
          
          $result=json_encode(array('success' => true,'msg'=>'Votre panier à bien etait mis à jour!'));
        } 
        else 
        {
          $result=null;
        }
        echo json_encode($result);
      }      
      /****************************Récuperer les produits du panier************************************ */
if ($postjson['actions']=="getCartProducts")
{
  $username=$postjson['username'];
  $sql="SELECT *, qte FROM produit, panier_details WHERE produit.id_produit= panier_details.id_produit AND panier_details.username='$username'";
  $result=$db->query($sql); 

  $check=mysqli_num_rows($result);

  $res['produit_qte']=[];

  if($check >0)
  {     
   
        while($cat=$result->fetch_assoc())
    {
      
      $cat['image_min']=$localhost. $cat['image_min'];
      $res['produit_qte'][]=$cat;
     
    }
    echo json_encode($res);
  } 

  else 
  {
   echo json_encode(null);
  }
   
  

}
/******************************************Remove from cart*********************************** */
if ($postjson['actions']=="RemoveFromCart")
{ 
  $username=$postjson['username'];
  $id_produit=$postjson['id_produit'];
  $prix=$postjson['prix'];

 $sql4="UPDATE panier SET total=total-'$prix' WHERE username='$username'";
  $query4=$db->query($sql4);

  

  $sql1="SELECT qte FROM panier_details WHERE username='$username' AND id_produit ='$id_produit'";
  $query1=mysqli_query($db,$sql1);
  $check=mysqli_num_rows($query1); 
  if($check >0)
     {     
      $res=mysqli_fetch_array($query1);
      $qte=$res['qte'];
     
         if($qte>1)

           {
             
             $sql2="UPDATE panier_details SET qte=qte-1 WHERE username='$username' AND id_produit ='$id_produit'";
             $query2=$db->query($sql2);
             if($query2)
               {
                 $result=json_encode(array('success' => true,'msg'=>'Votre panier à bien etait mis à jour!'));
               }
             else 
               {
                $result=json_encode(array('success' => false,'msg'=>'Oups Erreur lors de la mise à jour de votre panier'));
               }
           }
         else 
          {
           
          $sql3="DELETE FROM panier_details WHERE username ='$username' AND id_produit='$id_produit'";
          $query3=$db->query($sql3);

          if($query3)
            {
               $result=json_encode(array('success' => true,'msg'=>'Ce produit à bien etait supprimé!'));
            }
         else 
           {
               $result=json_encode(array('success' => false,'msg'=>'Oups Erreur lors de la supression du panier'));
           }
           }
        
        echo json_encode($result);
      }
       else 
       json_encode(null);
 
    }
   
  
/***************************************Récuperer les wilayas*********************** */
if($postjson['actions']=="getWilayas")
      {
       
        $sql="SELECT *  FROM wilaya WHERE affiche =1";
        $result=$db->query($sql); 
      
        $check=mysqli_num_rows($result);
      
        $res['wilaya']=[];
      
        if($check >0)
        {     
         
            while($cat=$result->fetch_assoc())
          {
            
            $res['wilaya'][]=$cat;
           
          }
          echo json_encode($res);
        } 
      
        else 
        {
         echo json_encode(null);
        }
      }    
      /*****************************Recuperer Total Panier********************** */
      if($postjson['actions']=="getTotalCard")
      {
        $username=$postjson['username'];
        $sql="SELECT * FROM panier WHERE username ='$username'";
        $result=$db->query($sql); 
        $res['panier']=[];
          
        while($cat=$result->fetch_assoc())
        {
          
          $res['panier'][]=$cat;
         
        }
           
           echo json_encode($res);
        
        
      }    
/*********************Recuperer frais livraison wilaya******************************** */
if($postjson['actions']=="getWilayaPrice")
{
  $wilaya=$postjson['wilaya'];
  $sql="SELECT * FROM wilaya WHERE nom_wilaya ='$wilaya'";
  $result=$db->query($sql); 
  $res['wilaya']=[];
    
  while($cat=$result->fetch_assoc())
  {
    
    $res['wilaya'][]=$cat;
   
  }
     
     echo json_encode($res);
  
  
}    
/************************************Inserer une commande*************************** */
if($postjson['actions']=="insertCommand")
{
  $username=$postjson['username'];
 
  $total=$postjson['total'];
  $adresse=$postjson['adresse'];
  $wilaya=$postjson['wilaya'];
  $date= date('Y-m-d');
  
  $sql="INSERT INTO commande (username,wilaya,adr_livraison,total,date_cmd) VALUES('$username','$wilaya','$adresse','$total','$date')";
  $result=$db->query($sql); 

  
  $sqlMaxid="SELECT max(id_cmd) FROM commande";
  $resultMaxid=$db->query($sqlMaxid); 

  $res=mysqli_fetch_array($resultMaxid);
  $id=$res['max(id_cmd)'];
   
 $sql1="SELECT * FROM panier_details WHERE username='$username'";
  $result1=$db->query($sql1); 

  //$res['panier_details']=[];

 while($cat=$result1->fetch_assoc())
  {
  
  $id_produit=$cat['id_produit'];
  $qte=$cat['qte'];
  $sql2="INSERT INTO commande_details(id_cmd,id_produit,qtt_cmd)VALUES('$id','$id_produit','$qte')";
  $result2=$db->query($sql2);  
  
  }
   $sql3="DELETE FROM panier_details WHERE username='$username'";
   $result3=$db->query($sql3);  
   $sql4="UPDATE panier SET etat_panier=0 , total =0 WHERE username='$username'";
   $result4=$db->query($sql4);  

   if($result)
    {
     $result=json_encode(array('success' => true,'msg'=>'Votre commande à bien été enregistrer !'));
    }
   else
   {
    $result=null;
   }
   echo json_encode($result);
  
  
}    
/**************************Insert into favorite******************* */
if($postjson['actions']=="insertIntoFavorite")
{
  $username=$postjson['username'];
  $id_produit=$postjson['id_produit'];

  $sql="INSERT INTO favoris(username,id_produit) VALUES('$username','$id_produit')";
  $result=$db->query($sql); 
  if($result)
            {
               $result=json_encode(array('success' => true,'msg'=>'Votre liste de favoris à été mise à jour'));
            }
         else 
           {
               $result=json_encode(array('success' => false,'msg'=>'Oups Erreur'));
           }
  
     
  echo json_encode($result);
  
  
}    
/************************Retirer des favoris********************************* */
if($postjson['actions']=="DeleteFromFavorite")
{
  $username=$postjson['username'];
  $id_produit=$postjson['id_produit'];

  $sql="DELETE FROM favoris WHERE username='$username' AND id_produit='$id_produit'";
  $result=$db->query($sql); 
  if($result)
            {
               $result=json_encode(array('success' => true,'msg'=>'Votre liste de favoris à été mise à jour'));
            }
         else 
           {
               $result=json_encode(array('success' => false,'msg'=>'Oups Erreur'));
           }
  
     
  echo json_encode($result);
  
  
}    
/*****************est il dans mes favoris ?************************************ */
if($postjson['actions']=="IsOnFavorite")
{
  $username=$postjson['username'];
  $id_produit=$postjson['id_produit'];

  $sql="SELECT * FROM favoris WHERE username='$username' AND id_produit='$id_produit'";
  $result=$db->query($sql); 
  $check=mysqli_num_rows($result);
  if($check>0)
            {
               $result=true;
            }
         else 
           {
               $result=false;
           }
  
     
  echo json_encode($result);
  
}   
/**************Recuperer les produits des favoris*************************** */ 
if($postjson['actions']=="getFavoriteProducts")
{
  $username=$postjson['username'];
  

  $sql="SELECT * FROM produit WHERE id_produit IN ( SELECT id_produit FROM favoris WHERE username='$username')";
  $result=$db->query($sql); 
  $check=mysqli_num_rows($result);
  $res['produit']=[];
  if($check>0)
            {
              while($cat=$result->fetch_assoc())
              {
                $cat['image_min']=$localhost. $cat['image_min'];
                $res['produit'][]=$cat;
              }
              echo json_encode($res);
            }
         else 
           {
            echo json_encode(null);
           }
}   
/********************Récuperer le prix min max par categorie**************************** */
if ($postjson['actions']=="getLowerUperCat")
{
 
  $idCat=$postjson['idCat'];
  $sql="SELECT max(prix), min(prix) FROM produit WHERE id_produit IN (SELECT id_produit FROM produit WHERE id_scategorie IN (SELECT id_scategorie FROM scategorie WHERE id_categorie ='$idCat'))";
  $result=$db->query($sql); 
  $res=mysqli_fetch_array($result);
  $max=$res['max(prix)'];
  $min=$res['min(prix)'];
  

  $MinMax=json_encode(array('Min' => $min,'Max'=>$max));
  echo($MinMax);
     
}
/*********************Récuper le prix min max de la sous categorie******************* */
if ($postjson['actions']=="getLowerUperSubCat")
{
 
  $idCat=$postjson['idCat'];
  $sql="SELECT max(prix), min(prix) FROM produit WHERE id_produit IN (SELECT id_produit FROM produit WHERE id_scategorie ='$idCat')";
  $result=$db->query($sql); 
  $res=mysqli_fetch_array($result);
  $max=$res['max(prix)'];
  $min=$res['min(prix)'];
  

  $MinMax=json_encode(array('Min' => $min,'Max'=>$max));
  echo($MinMax);
     
}