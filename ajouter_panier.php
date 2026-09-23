<?php
session_start();
$pdo=new PDO("mysql:host=127.0.0.1;dbname=jardin_des_agriculteurs;charset=utf8mb4","root","",[
 PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
]);
$id=(int)($_POST['id_produit']??0);
$q=max(1,(int)($_POST['quantite']??1));
$stmt=$pdo->prepare("SELECT id_produit,stock,statut FROM produits WHERE id_produit=? LIMIT 1");
$stmt->execute([$id]); $p=$stmt->fetch();

if(!$p || $p['statut']!=='disponible' || (int)$p['stock']<=0){header("Location: produits.php?erreur=indisponible");exit;}
if(!isset($_SESSION['panier'])) $_SESSION['panier']=[];
$ancienne=(int)($_SESSION['panier'][$id]??0);
$_SESSION['panier'][$id]=min($ancienne+$q,(int)$p['stock']);
header("Location: panier.php"); exit;
?>
