<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>НБКИ-выгрузка</title>

  <style>
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
    }
  </style>

</head>
<body>
  <table border="1" cellpadding="10" cellspacing="0">
    <tr>
      <td>
        <form action="http://localhost:8000/index.php" method="post">
          <div id="reportTypeBlock">
            <label for="reportTypeBlock">Тип выгрузки:</label>
              <input type="radio" id="Google" name="reportType" value="Google" onclick="showContractField()" checked>
              <label for="Google">Google</label>
            <input type="radio" id="MSSQL" name="reportType" value="MSSQL" onclick="showContractField()">
            <label for="MSSQL">MSSQL</label>
          </div>
          <br>
          <div id="periodStartBlock">
            <label for="periodStart">Начало периода:</label>
            <input type="date" id="periodStart" name="periodStart" value=<?= $_SESSION['periodStart'] ?> required>
          </div>
          <br>
          <div id="requestTypeBlock">
            <label for="requestType">Выгрузка:</label>
            <select id="requestType" name="requestType" onclick="showContractField()" required>
              <option value="ALL">по всем портфелям</option>
              <option value="TARGET">по договору</option>
              <option value="DELETE">по договору - удаление</option>
            </select>
          </div>
          <br>
          <div id="contractNumberBlock" style="visibility:hidden;">
            <label for="contract">Договор:</label>
            <input id="contract" name="contract">
          </div>
          <br>
          <input type="submit" value="ОК">
          [последняя выгрузка: <span style="font-size: 80%;font-weight: bold;"><?= $_SESSION['periodEnd'] ?></span>]
        </form>
      </td>
    </tr>
  </table>
  <script>
    function showContractField() {
      var reportTypeMSSQL = document.getElementById("MSSQL");
      var requestType = document.getElementById("requestType");

      var contractNumberBlock = document.getElementById("contractNumberBlock");

      if (reportTypeMSSQL.checked && requestType.value !== "ALL") {
        contractNumberBlock.style.visibility = "visible";
      } else {
        contractNumberBlock.style.visibility = "hidden";
      }
    }
  </script>
</body>
</html>
