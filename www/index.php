<!DOCTYPE html>
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
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function popupAlert($message)
{
    echo "<script>alert('$message');</script>";
}

function czyWeekend($data)
{
    return (date('N', strtotime($data)) >= 6);
}

$dzis = date('Y-m-d');

if (isset($_GET['liczbaLadunkow']))
    $liczbaLadunkow = $_GET['liczbaLadunkow'];
else
    $liczbaLadunkow = 1;


function wyslij($liczbaLadunkow)
{
    try {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['MAIL_PORT'];
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];

        $mail->isHTML(true);
        $mail->setFrom("transport@samoloty.com", "Pawel");

        if ($_POST['inputGroupSelectSamolot'] == '35000') {
            $do = "airbus@samoloty.com";
            $samolot = "Airbus A380";
        } else {
            $do = "boeing@samoloty.com";
            $samolot = "Boeing 747";
        }
        $mail->addAddress($do);
        $mail->Subject = ("Transport");

        $body = "<table> ";
        $body .= " <tr> <th> Transport z </th> <th>" . $_POST['transport_z'] . "</th></tr>";
        $body .= " <tr> <th> Transport do </th> <th>" . $_POST['transport_do'] . "</th></tr>";
        $body .= " <tr> <th> Data transportu </th> <th>" . $_POST['data_transportu'] . "</th></tr>";
        $body .= "</table>";


        $conn = mysqli_connect($_ENV['MYSQL_DB'], "root", $_ENV['MYSQL_ROOT_PASSWORD'], $_ENV['MYSQL_DB_NAME']);

        for ($i = 0; $i < count($_FILES['dokumenty']['tmp_name']); $i++)
            $dokumenty = $_FILES['dokumenty']['name'][$i];

        $query = 'INSERT INTO transport (transport_z, transport_do, typ_samolotu, data_transportu, dokumenty) values ("'
            . $_POST["transport_z"] . '","' . $_POST["transport_do"] . '"," ' . $samolot . '"," ' . $_POST["data_transportu"] . '","' . $dokumenty . '")';
        if (!$conn->query($query))
            printf("Błąd: %s<br>\n", $conn->error);


        $last_id = $conn->insert_id;

        $x = 0;
        $body .= "<table>";
        do {
            $body .= " <tr> <th> Ladunek nr </th> <th>" . $x + 1 . "</th></tr>";
            $body .= " <tr> <th> Nazwa ladunku </th> <th>" . $_POST["nazwa_ladunku$x"] . "</th></tr>";
            $body .= " <tr> <th> Ciezar ladunku </th> <th>" . $_POST["ciezar_ladunku$x"] . "</th></tr>";
            $body .= " <tr> <th> Typ ladunku </th> <th>" . $_POST["typ_ladunku$x"] . "</th></tr>";

            $query = 'INSERT INTO ladunek (transport_id, nazwa, ciezar_ladunku, typ_ladunku) values ("'
                . $last_id . '","' . $_POST["nazwa_ladunku$x"] . '"," ' . $_POST["ciezar_ladunku$x"] . '"," ' . $_POST["typ_ladunku$x"] . '")';
            if (!$conn->query($query))
                printf("Błąd: %s<br>\n", $conn->error);
            $x++;
        } while ($x < $liczbaLadunkow);
        $body .= "</table>";

        $mail->Body = $body;

        for ($i = 0; $i < count($_FILES['dokumenty']['tmp_name']); $i++)
            $mail->addAttachment($_FILES['dokumenty']['tmp_name'][$i], $_FILES['dokumenty']['name'][$i]);

        if ($mail->send()) {
            popupAlert("Pomyślnie wysłano zgłoszenie");
        } else {
            popupAlert("Błąd wysyłaniu zgłoszenia: " . $mail->ErrorInfo);
        }


        $conn->close();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

$x = 0;
if (isset($_POST['ciezar_ladunku0'])) {
    $formularzPoprawny = true;

    if (!czyWeekend($_POST['data_transportu'])) {
        do {
            if ($_POST["ciezar_ladunku$x"] > $_POST['inputGroupSelectSamolot']) {
                popupAlert("Za duży cieżar w paczce nr " . $x + 1);
                $formularzPoprawny = false;
                break;
            }
            $x++;
        } while ($x < $liczbaLadunkow);

        if ($formularzPoprawny)
            wyslij($liczbaLadunkow);
    } else
        popupAlert("Data transportu może odbywać się tylko w dni robocze");
}

?>

<form action="" method="POST" enctype="multipart/form-data">

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
        <select class="custom-select" id="inputGroupSelectSamolot" name="inputGroupSelectSamolot">
            <option selected value="35000">Airbus A380 (Maksymalna waga pojedynczego ładudunku 35 ton)</option>
            <option value="38000">Boeing 747 (Maksymalna waga pojedynczego ładudunku 38 ton)</option>
        </select>
    </div>

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <label class="input-group-text">Dokumenty przewozowe</label>
        </div>
        <input type="file" class="form-control" id="dokumenty" name="dokumenty[]"
               accept=".pdf, .jpg, .png, .doc, .docx" multiple>
    </div>

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <label class="input-group-text">Data transportu</label>
        </div>
        <input type="date" id="data_transportu" name="data_transportu"
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
                    <input type="number" step="0.01" class="form-control" id="ciezar_ladunku" name="ciezar_ladunku' . $i . '" max="38000"
                        required placeholder="Wpisz ciężar">
              </div>');

        echo('<div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <label class="input-group-text">Typ ładunku</label>
                     </div>
                    <select class="custom-select" id="inputGroupSelectTypLadunku" name = "typ_ladunku' . $i . '">
                        <option value="ladunek zwykly">Ładunek zwykły</option>
                        <option value="ladunek niebezpieczny">Ładunek niebezbieczny</option>
                    </select>
              </div>');

        $i++;
    } while ($i < $liczbaLadunkow)

    ?>

    <div class="btn-group" role="group" aria-label="przyciski">
        <a href="?liczbaLadunkow=<?php echo $liczbaLadunkow + 1 ?>" class="btn btn-outline-primary">Dodaj kolejny
            ładunek</a>
        <?php if ($liczbaLadunkow > 1)
            echo '<a href="?liczbaLadunkow=' . $liczbaLadunkow - 1 . '" class="btn btn-outline-primary">Usuń ostatni ładunek</a>';
        ?>

        <button type="submit" class="btn btn-outline-primary">Wyślij</button>
    </div>
</form>

</body>
</html>