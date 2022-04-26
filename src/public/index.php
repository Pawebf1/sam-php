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
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__,2));
$dotenv->load();


function popupAlert($message)
{
    echo "<script>alert('$message');</script>";
}

function isWeekend($data)
{
    return (date('N', strtotime($data)) >= 6);
}

$today = date('Y-m-d');

if (isset($_GET['liczbaLadunkow']))
    $packageNumber = $_GET['liczbaLadunkow'];
else
    $packageNumber = 1;


function send($liczbaLadunkow)
{
    if ($_POST['inputGroupSelectSamolot'] == '35000') {
        $to = "airbus@samoloty.com";
        $airplane = "Airbus A380";
    } else {
        $to = "boeing@samoloty.com";
        $airplane = "Boeing 747";
    }

    try {
        sendEmail($liczbaLadunkow, $to);
        saveDB($liczbaLadunkow, $airplane);
        popupAlert("Pomyślnie wysłano zgłoszenie");
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function sendEmail($packageNumber, $to)
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
        $mail->addAddress($to);
        $mail->Subject = ("Transport");

        $body = "<table> ";
        $body .= " <tr> <th> Transport z </th> <th>" . $_POST['transport_z'] . "</th></tr>";
        $body .= " <tr> <th> Transport do </th> <th>" . $_POST['transport_do'] . "</th></tr>";
        $body .= " <tr> <th> Data transportu </th> <th>" . $_POST['data_transportu'] . "</th></tr>";


        $x = 0;
        do {
            $body .= " <tr> <th> Ladunek nr </th> <th>" . $x + 1 . "</th></tr>";
            $body .= " <tr> <th> Nazwa ladunku </th> <th>" . $_POST["nazwa_ladunku$x"] . "</th></tr>";
            $body .= " <tr> <th> Ciezar ladunku </th> <th>" . $_POST["ciezar_ladunku$x"] . "</th></tr>";
            $body .= " <tr> <th> Typ ladunku </th> <th>" . $_POST["typ_ladunku$x"] . "</th></tr>";
            $x++;
        } while ($x < $packageNumber);
        $body .= "</table>";

        $mail->Body = $body;

        for ($i = 0; $i < count($_FILES['dokumenty']['tmp_name']); $i++)
            $mail->addAttachment($_FILES['dokumenty']['tmp_name'][$i], $_FILES['dokumenty']['name'][$i]);

        $mail->send();

    } catch (Exception $e) {
        echo $e->getMessage();
    }

}


function saveDB($packageNumber, $airplane)
{

    try {
        $conn = mysqli_connect($_ENV['MYSQL_DB'], "root", $_ENV['MYSQL_ROOT_PASSWORD'], $_ENV['MYSQL_DB_NAME']);
        $dokumenty = "";
        for ($i = 0; $i < count($_FILES['dokumenty']['tmp_name']); $i++)
            $dokumenty .= $_FILES['dokumenty']['name'][$i] . " ";

        $query = 'INSERT INTO transport (transport_z, transport_do, typ_samolotu, data_transportu, dokumenty) values ("'
            . $_POST["transport_z"] . '","' . $_POST["transport_do"] . '"," ' . $airplane . '"," ' . $_POST["data_transportu"] . '","' . $dokumenty . '")';
        if (!$conn->query($query))
            printf("Błąd: %s<br>\n", $conn->error);

        $last_id = $conn->insert_id;
        $x = 0;
        do {
            $query = 'INSERT INTO ladunek (transport_id, nazwa, ciezar_ladunku, typ_ladunku) values ("'
                . $last_id . '","' . $_POST["nazwa_ladunku$x"] . '"," ' . $_POST["ciezar_ladunku$x"] . '"," ' . $_POST["typ_ladunku$x"] . '")';
            if (!$conn->query($query))
                printf("Błąd: %s<br>\n", $conn->error);
            $x++;
        } while ($x < $packageNumber);
        $conn->close();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}


$x = 0;
if (isset($_POST['ciezar_ladunku0'])) {
    $formValid = true;

    if (!isWeekend($_POST['data_transportu'])) {
        do {
            if ($_POST["ciezar_ladunku$x"] > $_POST['inputGroupSelectSamolot']) {
                popupAlert("Za duży cieżar w paczce nr " . $x + 1);
                $formValid = false;
                break;
            }
            $x++;
        } while ($x < $packageNumber);

        if ($formValid)
            send($packageNumber);
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
               value="<?php echo $today ?>"
               min="<?php echo $today ?>">
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
    } while ($i < $packageNumber)

    ?>

    <div class="btn-group" role="group" aria-label="przyciski">
        <a href="?liczbaLadunkow=<?php echo $packageNumber + 1 ?>" class="btn btn-outline-primary">Dodaj kolejny
            ładunek</a>
        <?php if ($packageNumber > 1)
            echo '<a href="?liczbaLadunkow=' . $packageNumber - 1 . '" class="btn btn-outline-primary">Usuń ostatni ładunek</a>';
        ?>

        <button type="submit" class="btn btn-outline-primary">Wyślij</button>
    </div>
</form>

</body>
</html>