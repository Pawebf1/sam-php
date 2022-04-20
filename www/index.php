<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css'
          integrity='sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm' crossorigin='anonymous'>
    <title>Sam php</title>
</head>
<body>
<br>
<?php

$dzis = date('Y-m-d');
if (isset($_GET['liczbaLadunkow']))
    $liczbaLadunkow = $_GET['liczbaLadunkow'];
else
    $liczbaLadunkow = 1;

?>
<form action="" method="POST">

    <label class="input-group-text">Informacje ogólne ładunku</label>
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <label class="input-group-text">Transport z</label>
        </div>
        <input type="text" class="form-control" id="transport_z" name="transport_z" required
               placeholder="Wpisz miasto">
    </div>

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <label class="input-group-text">Transport do</label>
        </div>
        <input type="text" class="form-control" id="transport_do" name="transport_do" required
               placeholder="Wpisz miasto">
    </div>

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <label class="input-group-text">Typ samolotu</label>
        </div>
        <select class="custom-select" id="inputGroupSelectSamolot">
            <option value="Airbus A380">Airbus A380 (Maksymalna waga pojedynczego ładudunku 35 ton)</option>
            <option value="Boeing 747">Boeing 747 (Maksymalna waga pojedynczego ładudunku 38 ton)</option>
        </select>
    </div>

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <label class="input-group-text">Dokumenty przewozowe</label>
        </div>
        <input type="file" class="form-control" id="dokumentyPrzewozowe" name="dokumentyPrzewozowe"
               accept=".pdf, .jpg, .png, .doc, .docx" multiple placeholder="Dodaj pliki">
    </div>

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <label class="input-group-text">Data transportu</label>
        </div>
        <input type="date" id="dataTransportu" name="dataTransportu"
               value="<?php echo $dzis ?>"
               min="<?php echo $dzis ?>">
    </div>


    <label class="input-group-text">Informacje poszczególnych ładunków</label>

    <?php
    $i = 0;
    do {
        echo("<label class='input-group-text'>Ładunek nr " . $i + 1 . "</label>");
        echo('<div class="input-group mb-3">
                <div class="input-group-prepend">
                    <label class="input-group-text">Nazwa ładunku</label>
                 </div>
                <input type="text" class="form-control" id="nazwa_ladunku" name="nazwa_ladunku' . $i . '" required
                    placeholder="Wpisz nazwę">
               </div>');

        echo('<div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <label class="input-group-text">Ciężar ładunku w kg</label>
                    </div>
                    <input type="number" step="0.01" class="form-control" id="ciezar_ladunku" name="ciezar_ladunku' . $i . '" max="38"
                        required placeholder="Wpisz ciężar">
              </div>');

        echo('<div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <label class="input-group-text">Typ ładunku</label>
                     </div>
                    <select class="custom-select" id="inputGroupSelectTypLadunku' . $i . '">
                        <option value="ladunek_zwykly">Ładunek zwykły</option>
                        <option value="ladunek_niebezpieczny">Ładunek niebezbieczny</option>
                    </select>
              </div>');

        $i++;
    } while ($i < $liczbaLadunkow)

    ?>

    <div class="btn-group" role="group" aria-label="przyciski">
        <a href="?liczbaLadunkow=<?php echo $liczbaLadunkow + 1 ?>" class="btn btn-outline-primary">Dodaj kolejny
            ładunek</a>
        <a href="?liczbaLadunkow=<?php echo $liczbaLadunkow - 1 ?>" class="btn btn-outline-primary">Usuń ostatni
            ładunek</a>
        <button type="submit" class="btn btn-outline-primary">Wyślij</button>
    </div>
</form>
</body>
</html>