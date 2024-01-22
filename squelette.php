<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Anime</title>
  <link rel="stylesheet" href="form.css" type="text/css" >
  <meta charset = "UTF-8">
</head>
<body>
  <header>
    <h1>Anime</h1>
  </header>
  <main>
    <nav class="menu">
      <a href="index.php?action=insert">Ajouter un animé</a>
      <a href="index.php?action=liste">Liste des animés</a>
    </nav>
    <h2><?php echo $zoneTitre; ?></h2>
    <div class=ZP><?php echo $zonePrincipale; ?></div>
  </main>
  <footer>
		<p>Lien vers mon adresse mail : <a href="mailto:ilan.lebris@etu.unicaen.fr">ilan.lebris@etu.unicaen.fr</a></p>
		<p>Lien vers l'<a href="https://www.unicaen.fr/"><abbr title="Université de Caen Normandie">UCN</abbr></a></p>
		<p>Pour plus d'informations: <a href="index.php?action=apropos">à propos</a></p>
	</footer>
</body>
</html>
 