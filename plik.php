<?php
$host = "localhost"; // adres serwera bazy danych
$port = "5432"; // domyślny port PostgreSQL
$dbname = "postgres"; // nazwa bazy danych
$user = "postgres"; // użytkownik bazy danych
$password = "maslo555"; // hasło użytkownika bazy danych

$conn=pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if(!$conn){
    echo "Blad polaczenia";
}else{
    echo "poloczono";
}

$query="SELECT * FROM recipes";
$result =pg_query($conn,$query);

if(!$result){
    echo "Blad zapytania:" . preg_last_error();
    exit;
}

echo "<table>
<tr>
<th>ID</th>
<th>Opis</th>
</tr>";

while ($row = pg_fetch_assoc($result)) {
    echo "<tr>
    <td>".$row['id']."</td>
    <td>" .$row['title']."</td>
    <td>" .$row['instructions']."</td>;
    </tr>";
}
echo"</table>";

pg_close($conn);
?>