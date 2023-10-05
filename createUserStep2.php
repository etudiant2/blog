<?php
session_start();
try
    {
        $db = new PDO('mysql:host=localhost:3306;dbname=blog;charset=utf8', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); // On émet une alerte à chaque fois qu'une
    }
    catch(Exception $e)
    {
        die('Erreur : '.$e->getMessage());
    }
?>
<html lang="fr">
<head>
    <title></title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/bootstrap.css" />
    <link rel="stylesheet" href="css/style.css" />
</head>


<body>
<header>
    <div class="row">
        <div class="col-12">
            <nav class="nav justify-content-end">
                <a class="nav-link" href="login.php">Se connecter</a>
                <a class="nav-link" href="account.php">M'inscrire</a>
            </nav>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-12 text-center">
            <h1>Mon super blog ! S'inscrire</h1>
        </div>
    </div>
</header>


<?php
// Vérifier si le login existe déjà
$login = htmlspecialchars( $_POST['login'] );
$password = htmlspecialchars( $_POST['password'] );
$passwordConfirm = htmlspecialchars( $_POST['passwordConfirm'] );

$req = $db->prepare( 'select * from users2 where pseudo =:pseudo' );
$req->execute( [':pseudo'=>$login] );
if( $req->rowCount() ) {
    header( 'Location: createUserStep1.php?pseudo=1' );
    exit();
}

// Vérifier le mot de passe et la confirm
if( strlen( $password ) < 8 ) {
    header( 'Location: createUserStep1.php?invalidpass=1' );
    exit();
}
if( $password != $passwordConfirm ) {
    header( 'Location: createUserStep1.php?invalidconfirm=1' );
    exit();
} 


$salt = random_bytes( SODIUM_CRYPTO_PWHASH_SALTBYTES );
$passHash = sodium_crypto_pwhash( 
    SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES, 
    $password, 
    $salt, 
    SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
    SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
    SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
);

$req = $db->prepare( 
    "INSERT INTO users2( pseudo, password ) VALUE( :pseudo, :password )"
 );
$isInsertOk = $req->execute([
    ':pseudo'   => $login,
    ':password' => bin2hex( $passHash ) 
 ]);
 if( !$isInsertOk ) {
    echo "Erreur lors de l'enregistrement";
    die;
 }


?>


<section class="container mt-5">
    <div class="row">
        <div class="col-12">
            <form name="accesform" method="post" action="validUser.php" enctype="multipart/form-data">

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="photo" name="photo">
                        <label class="custom-file-label" for="photo">Choisissez votre photo de profil</label>
                    </div>
                </div>
                <div class="mb-3 row justify-content-end">
                    <div class="col-sm-4">
                        <button type="submit" class="btn btn-primary mb-3">Valider</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</section>



<footer class="container">

</footer>


</body>