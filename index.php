<?php
                    require_once 'users.php';
                    if (isset($_GET['sortgroup']) && $_GET['sortgroup'] == 'apt') {
                        $ldap_dn = 'OU=ou", OU=ou1, DC=dc';
                    }
                    else {
                        $ldap_dn = 'OU=ou1, DC=dc';
                    }
                    try {
                        $ad = new ActiveDirectory('ldap://10.50.1.200', 'login', 'password', $ldap_dn);
                        //исключения. На данном этапе был список пользователей для исключений. Почистила для сохранения некоторой перс. информации
                        $excluded_users = ["1", "2", "3"];
                        
                        $exclusion_filters = array_map(fn($user) => "(!(sAMAccountName=$user))", $excluded_users);
                        $filter = "(&(objectClass=user)(!(useraccountcontrol:1.2.840.113556.1.4.803:=2))" . implode("", $exclusion_filters) . ")";
                        $errorfiltered = "(&(objectClass=user)(samaccountname=amatoryus))";
                        $users = $ad->getUsers($filter);
                        $errusers = $ad->getUsers($errorfiltered);
                        // сорт алфавит
                            usort($users, function ($a, $b)  {
                            $pattern = '/[А-Яа-яЁё]/u';
                            $a_is_russian = preg_match($pattern, $a['cn']);
                            $b_is_russian = preg_match($pattern, $b['cn']);

                            if ($a_is_russian && !$b_is_russian) {
                                return -1;  
                            }
                            if (!$a_is_russian && $b_is_russian) {
                            return 1; 
                            }
                        return strcasecmp($a['cn'], $b['cn']);
                            });
                    }          
                    catch (Exception $e) {
                        die("Ошибка: " . $e->getMessage());
                    }
                    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="Author" content="Alexandra Petrenko">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Телефонный справочник Кубаньфармация</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css?v=<?=time(); ?>">
    <link rel="stylesheet" href="print.css?v=<?=time(); ?>" media="print"/>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    </head>
<body> 
                <!--Информация для печати-->
    <div class="infomenu"><div class='infoblock'><h1>Справочник сотрудников 
    <br>Кубаньфармация</h1><span id="infodate" class="stafftable_columns"></span></div>
<img class="hidden" src="images/logo.png"  alt="Логотип Кубаньфармация"></div>
    <script>
        const todaydate = new Date();
        document.getElementById('infodate').textContent = `Дата создания: ${todaydate.toLocaleDateString('ru-RU')}`;
        </script>
        <!--Основная часть-->
    <div class="mainwindow">
        <table class="main">
          <tbody>
            <tr>
                <td class="filters" > 
                    <div id="all" class="tab">
                        <a href="?sortgroup=all">Все</a>
                    </div>
                    <div id="office" class="tab">
                        <a href="?sortgroup=office">Офис</a>
                    </div>
                    <div id="apt" class="tab">
                        <a href="?sortgroup=apt">Аптеки</a>
                    </div>
                    </div>
                        <div id='search' class="tab">
                            <script>
                                const urlpar = new URLSearchParams(window.location.search);
                        const sortgroupvalue = urlpar.get('sortgroup');
                        let linkHTML = "";
                                if(sortgroupvalue === 'office')
                    {
                        linkHTML = '<a href="?sortgroup=office&sort=search">Поиск</a>';
                    }
                    else if (sortgroupvalue === 'apt') {
                        linkHTML =  '<a href="?sortgroup=apt&sort=search">Поиск</a>';
                    }
                    else {
                        linkHTML =  '<a href="?sortgroup=all&sort=search">Поиск</a>';
                    }
                        document.write(linkHTML);
                                </script>
                        </div>                   
                        <div class="tab">
                            <img src="images/pdf.png" alt ="pdf"><a href="#" onclick="window.print()">Печать</a>
                     </div>
                </td>
            </tr>
            <object class='bmark' data="images/bmarksvg.svg" type="image/svg+xml" width="150" height="150"></object>
        <tr class="maintr">
        <script>
                    const sortvalue = urlpar.get('sort');
                    if (sortgroupvalue === 'office')
                        {
                            if (sortvalue === 'search') {
                            document.getElementById("office").classList.add('sel');
                            document.getElementById("apt").classList.remove('sel');
                            document.getElementById("all").classList.remove('sel');
                            document.getElementById("search").classList.add('sel');
                            }
                            else {
                            document.getElementById("office").classList.add('sel');
                            document.getElementById("apt").classList.remove('sel');
                            document.getElementById("all").classList.remove('sel');
                            document.getElementById("search").classList.remove('sel');
                            }
                        }
                    else if (sortgroupvalue === 'apt')
                        {
                           if (sortvalue === 'search') {
                            document.getElementById("office").classList.remove('sel');
                            document.getElementById("apt").classList.add('sel');
                            document.getElementById("all").classList.remove('sel');
                            document.getElementById("search").classList.add('sel');
                            }
                            else {
                                document.getElementById("office").classList.remove('sel');
                            document.getElementById("apt").classList.add('sel');
                            document.getElementById("all").classList.remove('sel');
                            document.getElementById("search").classList.remove('sel');
                            }
                        }
                        else
                        {
                            if (sortvalue === 'search') {
                            document.getElementById("office").classList.remove('sel');
                            document.getElementById("apt").classList.remove('sel');
                            document.getElementById("all").classList.add('sel');
                            document.getElementById("search").classList.add('sel');
                            }
                            else {
                            document.getElementById("office").classList.remove('sel');
                            document.getElementById("apt").classList.remove('sel');
                            document.getElementById("all").classList.add('sel');
                            document.getElementById("search").classList.remove('sel');
                            }
                        }
                    </script>
            <td>
            <script>  
                        $(document).ready(function(){
                        function searchEmployees() {
                            let value = $("#searchbar").val().toLowerCase();
                            $("#employees tbody tr").each(function() {
                                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                            });
                        }
                        $("#searchbtn").on("click", function() {
                            searchEmployees();
                        });
                        $("#searchbar").on("keypress", function(event) {
                            if (event.which === 13) { 
                            event.preventDefault(); 
                            searchEmployees();
                        }});
                    });
                    </script>
                    <form class="table_head"> 
                        
                    <a href="#"><button type="button" class="upbtn">^</button></a>
                <div class="table_headclass"> 
                   
                        <script>
                            $(document).ready(function() {
                                var header = $('.table_head');
                                var btn = $('.upbtn');
                                var headerheight = header.outerHeight();
                                var lastscroll = 0;

                                $(window).scroll(function() {
                                    var scrollPos = $(window).scrollTop();
                                    var scrolldown = scrollPos > lastscroll;
                                    var comphidd = scrollPos > headerheight;
                                    if (comphidd && scrolldown)
                                {
                                    header.addClass('fixed');
                                    btn.addClass('fixed');
                                }
                                else {
                                    header.removeClass('fixed');
                                    btn.removeClass('fixed');
                                    
                                }
                                lastcroll = scrollPos;
                                });
                            });

                        </script>
                    <br>
               
            <?php 
                if (isset($_GET['sortgroup']) && $_GET['sortgroup'] == 'office') {
                    if (isset($_GET['sort']) && $_GET['sort'] == 'search') {
                        ?>
                    <fieldset class="move_to_otd searching">
                    <legend>Поиск сотрудников</legend>
                <div id="searchdiv">
                    <input type="text" id="searchbar" placeholder="Поиск">
                    <button type="button" id="searchbtn"></button>
                </div>
                    <?php } else {
               ?>
                <fieldset class="move_to_otd">
                    <legend>Быстрый переход на отдел</legend>
                    <div class="ullist">    
                <ul>
                    <li>
                            <a href="#Отделкатегорийногоменеджмента" class="in_link">ОКМ</a>
                        </li>
                        <li>
                            <a href="#Отделнаркотическихсредствипсихотропнныхвеществ" class="in_link">ОН</a>
                        </li>
                        <li>
                            <a href="#Отделосновногохранения" class="in_link">ООХ</a>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <a href="#Информационно-аналитическийотдел" class="in_link">ИАО</a>
                        </li>
                        <li>
                            <a href="#ОтделИнформационно-справочнойслужбы" class="in_link">ИСС</a>
                        </li>
                        <li>
                            <a href="#Информационныхтехнологий" class="in_link">ИТ</a>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <a href="#Отделльготноголекарственногообеспечения" class="in_link">ЛЛО</a>
                        </li>
                        <li>
                            <a href="#Отделветеринарныхпродаж" class="in_link">ОВП</a>
                        </li>
                        <li>
                            <a href="#Отделсопровожденияиразвитияаптечнойсети" class="in_link">ОРС</a>
                        </li>
                    </ul>
                    <ul>
                    <li>
                            <a href="#Отделприемкиготовыхлекарственныхсредств" class="in_link">ОП</a>
                        </li>
                        <li>
                            <a href="#Организационно-фармацевтическийотдел" class="in_link">ОФО</a>
                        </li>
                        <li>
                            <a href="#Контрольно-ревизионныйотдел" class="in_link">РВ</a>
                        </li>
</ul>
                        <ul>
                        <li>
                            <a href="#ОфисСочи" class="in_link">Офис Сочи</a>
                        </li>
                        <li>
                            <a href="#Отделкадров" class="in_link">Отдел кадров</a>
                        </li>
                        <li>
                            <a href="#ОтделЭкспедиции" class="in_link">Отдел Экспедиции</a>
                        </li>
                    </ul>
                        <ul>
                        <li>
                            <a href="#Руководство" class="in_link">Руководство</a>
                        </li>
                        <li>
                            <a href="#ОтделБухгалтерскогоучёта" class="in_link">Отдел бухгалтерского учета</a>
                        </li>
                        <li>
                            <a href="#Юридическийотдел" class="in_link">Юридический отдел</a>
                        </li>
                        
                    </ul>
                    <ul>
                        <li>
                            <a href="#Тендерныйотдел" class="in_link">Тендерный отдел</a>
                        </li>
                        <!--<li>
                            <a href="#Финансово-экономическийотдел" class="in_link">Финансово-экономический отдел</a>
                        </li>-->
                        <li>
                            <a href="#Административно-хозяйственныйотдел" class="in_link">Административно-хозяйственный отдел</a>
                        </li>
                    </ul>
                </div>
            <?php }} else if (isset($_GET['sortgroup']) && $_GET['sortgroup'] == 'apt'){
                if (isset($_GET['sort']) && $_GET['sort'] == 'search') {
                    ?>
                <fieldset class="move_to_otd searching">
                <legend>Поиск сотрудников</legend>
            <div id="searchdiv">
                <input type="text" id="searchbar" placeholder="Поиск">
                <button type="button" id="searchbtn"></button>
            </div>
            
                <?php } else {
                    ?>
                <fieldset class="move_to_otd filials">
                <legend>Номера филиалов и ОП</legend>
                <div class="ullist allfield">
                    <span class="info">
                <p>Филиалы: <span class="highlightinfo">90NN</span>, где NN - номер филиала (Филиал 10 - 9010, Филиал 3 - 9003 и т.д)</p>
                <p>ОП: <span class="highlightinfo">99NN</span>, где NN - номер ОП.</p>
                    </span>
                </div>
            <?php }} else {
                if (isset($_GET['sort']) && $_GET['sort'] == 'search') {
                    ?>
                <fieldset class="move_to_otd searching">
                <legend>Поиск сотрудников</legend>
            <div id="searchdiv">
                <input type="text" id="searchbar" placeholder="Поиск">
                <button type="button" id="searchbtn"></button>
            </div>
            
                <?php } else {
                    ?>
                <fieldset class="move_to_otd">
                    <legend>Быстрый переход на отдел</legend>
                    <div class="ullist alldep">
                    <ul>
                    <li>
                            <a href="#Отделкатегорийногоменеджмента" class="in_link">ОКМ</a>
                        </li>
                        <li>
                            <a href="#Отделнаркотическихсредствипсихотропнныхвеществ" class="in_link">ОН</a>
                        </li>
                        <li>
                            <a href="#Отделосновногохранения" class="in_link">ООХ</a>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <a href="#Информационно-аналитическийотдел" class="in_link">ИАО</a>
                        </li>
                        <li>
                            <a href="#ОтделИнформационно-справочнойслужбы" class="in_link">ИСС</a>
                        </li>
                        <li>
                            <a href="#Информационныхтехнологий" class="in_link">ИТ</a>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <a href="#Отделльготноголекарственногообеспечения" class="in_link">ЛЛО</a>
                        </li>
                        <li>
                            <a href="#Отделветеринарныхпродаж" class="in_link">ОВП</a>
                        </li>
                        <li>
                            <a href="#Отделсопровожденияиразвитияаптечнойсети" class="in_link">ОРС</a>
                        </li>
                    </ul>
                    <ul>
                    <li>
                            <a href="#Отделприемкиготовыхлекарственныхсредств" class="in_link">ОП</a>
                        </li>
                        <li>
                            <a href="#Организационно-фармацевтическийотдел" class="in_link">ОФО</a>
                        </li>
                        <li>
                            <a href="#Контрольно-ревизионныйотдел" class="in_link">РВ</a>
                        </li>
</ul>
                        <ul>
                        <li>
                            <a href="#ОфисСочи" class="in_link">Офис Сочи</a>
                        </li>
                        <li>
                            <a href="#Отделкадров" class="in_link">Отдел кадров</a>
                        </li>
                        <li>
                            <a href="#ОтделЭкспедиции" class="in_link">Отдел Экспедиции</a>
                        </li>
                    </ul>
                        <ul>
                        <li>
                            <a href="#Руководство" class="in_link">Руководство</a>
                        </li>
                        <li>
                            <a href="#ОтделБухгалтерскогоучёта" class="in_link">Отдел бухгалтерского учета</a>
                        </li>
                        <li>
                            <a href="#Юридическийотдел" class="in_link">Юридический отдел</a>
                        </li>
                        
                    </ul>
                    <ul>
                        <li>
                            <a href="#Тендерныйотдел" class="in_link">Тендерный отдел</a>
                        </li>
                      <!--<li>
                            <a href="#Финансово-экономическийотдел" class="in_link">Финансово-экономический отдел</a>
                        </li>-->
                        <li>
                            <a href="#Административно-хозяйственныйотдел" class="in_link">Административно-хозяйственный отдел</a>
                        </li>
                    </ul>
                    <hr>
                <ul>
                        <li>
                        <a href="#Филиалы" class="in_link">Филиалы</a>
                        </li>
                       
                    </ul>
                </div> 
            </ul>
            </div> 
                <br>
                    <?php }}?>
                </div>
                <br>
                </form>
                    <?php
// Определяем переменные из GET-запроса
$sortGroup = $_GET['sortgroup'] ?? 'all'; 
$sortType = $_GET['sort'] ?? '';

// Список отделов, которые нужно исключить для OFFICE
$excludedDepartmentsOffice = ["Филиалы", "FL", "RV", "ON", "OP", "HZ", "SM", "ORP", 
                              "BUH", "SL", "FE", "IT", "ADM", "Bez", "YO", "OF", 
                              "OK", "TL", "OM", "ISS", "IAO"];
$excludedDepartmentsAll = ["FL", "RV", "ON", "OP", "HZ", "SM", "ORP", 
                              "BUH", "SL", "FE", "IT", "ADM", "Bez", "YO", "OF", 
                              "OK", "TL", "OM", "ISS", "IAO"];                      

// Фильтрация пользователей
function filterUsers($users, $sortGroup) {
    return array_filter($users, function ($user) use ($sortGroup) {
        if ($sortGroup === 'all' || $sortGroup === 'office') {
            if (str_contains($user['distinguishedname'], "OU=Computers")) {
                return false;
            }
        }
        if ($sortGroup === 'office' && str_contains($user['distinguishedname'], "OU=Филиалы")) {
            return false;
        }
        return true;
    });
}

// Вывод таблицы сотрудников
function renderTable($users, $ital) {
    ?>
    <table id="employees" class="stafftable" cellpadding="4">
        <thead>
            <tr>
                <?php if (isset($_GET['sortgroup']) && $_GET['sortgroup'] == 'apt') {
                    ?>
                <th class="stafftable_columns">Наименование</th>
                <th class="stafftable_columns">ФИО</th>
                <?php } else { ?>
                <th class="stafftable_columns">ФИО</th>
                <th class="stafftable_columns">Должность</th>
                <?php } ?>
                <th class="stafftable_columns">E-mail</th>
                <th class="stafftable_columns">Внутренний</th>
                <th class="stafftable_columns">Рабочий</th>
                <th class="stafftable_columns">Мобильный</th>
            </tr>
        </thead>
        <tbody class="stafftable_body">
            <?php
             foreach ($users as $user): ?>
                <tr class="stafftable_even">
                    <td><div class="fio"><?= htmlspecialchars($user["cn"]) ?></div></td>
                    <td><div class="position"><?= htmlspecialchars($user["title"]) ?></div></td>
                    <td><span class="email"> <?php if ($user["mail"] === "Нет данных" ||  $user["mail"] === "отсутствует") { ?> <?= htmlspecialchars($user["mail"]) ?></div></td> <?php } else { ?> <a href="mailto:<?= htmlspecialchars($user["mail"]) ?>"><?= htmlspecialchars($user["mail"]) ?></a></span></td> <?php }?>
                    <td><span class="insidenumber"> <?php if ($user["ipphone"] === "Нет данных" || $user["ipphone"] === "отсутствует" ) { ?> <?= htmlspecialchars($user["ipphone"]) ?></div></td> <?php } else { ?> <a href="callto:<?= htmlspecialchars($user["ipphone"]) ?>"><?= htmlspecialchars($user["ipphone"]) ?></a></span></td> <?php }?>
                    <td><span class="citynumber"> <?php if ($user["telephonenumber"] === "Нет данных" || $user["telephonenumber"] === "отсутствует" ) { ?> <?= htmlspecialchars($user["telephonenumber"]) ?></div></td> <?php } else { ?> <a href="callto:<?= htmlspecialchars($user["telephonenumber"]) ?>"><?= htmlspecialchars($user["telephonenumber"]) ?></a></span></td> <?php }?>
                    <td><span class="mobilenumber"> <?php if ($user["mobile"] === "Нет данных" || $user["mobile"] === "отсутствует" ) { ?> <?= htmlspecialchars($user["mobile"]) ?></div></td> <?php } else { ?> <a href="callto:<?= htmlspecialchars($user["mobile"]) ?>"><?= htmlspecialchars($user["mobile"]) ?></a></span></td> <?php }?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}
if ($sortType == 'search') {
    $filteredUsers = filterUsers($users, $sortGroup);
    if ($sortGroup == 'apt') {
        renderTable($filteredUsers, 0);
    }
    else {
    renderTable($filteredUsers, 1);}
}
else {
$department = [];
foreach($users as $user) {
if (isset($user['distinguishedname'])) {
preg_match('/OU=([^,]+)/', $user['distinguishedname'], $matches);
$departmentclean = str_replace(['БУХ-', 'ИАО-','ИСС-','ИТ-',
'ЛЛО-','ОВП-','ОК-','ОКМ-','ОН-','ООХ-','ОП-','ОРС-','ОФО-','РВ-','ТО-',
'ФЕ-','ЮО-','ХЗ-', 'ГУП КК \"Кубаньфармация\"'], '', $matches);

$department = $departmentclean[1] ?? "Без отдела";

}
                else { $department = "Без отдела";}
                $departments[$department][] = $user;
            }
// Основная логика вывода
if ($sortGroup === 'all') {
    ksort($departments, SORT_LOCALE_STRING); // Сортировка по алфавиту
    if (isset($departments['Руководство'])) {
        $adminDepartment = ['Руководство' => $departments['Руководство']];
        unset($departments['Руководство']);
        $departments = $adminDepartment + $departments; // Объединяем с сохранением порядка
    }
    if (isset($departments['Филиалы '])) {
        $fildown = ['Филиалы ' => $departments['Филиалы ']];
        unset($departments['Филиалы ']);
        $departments = $departments + $fildown; 
    }
    foreach ($departments as $department => $users) {
        
        usort($users, function($a, $b) {
            $order = function($position) {
                if (stripos($position, 'Начальник') === 0) return 1;
                if (stripos($position, 'Заместитель') === 0 ) return 2;
                if (stripos($position, 'Главный бухгалтер') === 0) return 1;
                if (stripos($position, 'И.о') === 0) return 1;
                return 3;
            };
            return $order($a['title']) <=> $order($b['title']);
        });
        $department_id = str_replace(" ", "", $department);
        if (in_array($department_id, $excludedDepartmentsAll)) {
            continue;
        }
        if ($department === "Информационных технологий") {
            $filteredUsers = filterUsers($users, $sortGroup);
            ?>
            <div id="<?= $department_id; ?>" class="departmentname"><?= htmlspecialchars($department); ?></div>
            <?php renderTable($filteredUsers, 1); ?>
            <?php
        }
        else {
        $filteredUsers = filterUsers($users, $sortGroup);
        ?>
        <div id="<?= $department_id; ?>" class="departmentname"><?= htmlspecialchars($department); ?></div>
        <?php renderTable($filteredUsers, 0); ?>
        <?php
        }
    }
}elseif ($sortGroup === 'office') {
    ksort($departments, SORT_LOCALE_STRING); // Сначала обычная сортировка по алфавиту
    
    // Выносим "Руководство" отдел в начало массива
    if (isset($departments['Руководство'])) {
        $adminDepartment = ['Руководство' => $departments['Руководство']];
        unset($departments['Руководство']);
        $departments = $adminDepartment + $departments; // Объединяем с сохранением порядка
    }
    foreach ($departments as $department => $users) {
   
        usort($users, function($a, $b) {
            $order = function($position) {
                if (stripos($position, 'Начальник') === 0) return 1;
                if (stripos($position, 'Заместитель') === 0 ) return 2;
                if (stripos($position, 'Главный бухгалтер') === 0) return 1;
                if (stripos($position, 'И.о') === 0) return 1;
                return 3;
            };
            return $order($a['title']) <=> $order($b['title']);
        });
        
        $department_id = str_replace(" ", "", $department);
        if (in_array($department_id, $excludedDepartmentsOffice)) {
            continue;
        }
        if ($department === "Информационных технологий") {
            $filteredUsers = filterUsers($users, $sortGroup);
            ?>
            <div id="<?= $department_id; ?>" class="departmentname"><?= htmlspecialchars($department); ?></div>
            <?php renderTable($filteredUsers, 1); ?>
            <?php
        }
        else {
        $filteredUsers = filterUsers($users, $sortGroup);
        ?>
        <div id="<?= $department_id; ?>" class="departmentname"><?= htmlspecialchars($department); ?></div>
        <?php renderTable($filteredUsers, 0); ?>
        <?php
        }
    }} elseif ($sortGroup === 'apt') {
    foreach ($departments as $department => $users) {
        usort($users, function($a, $b) {
            preg_match('/\d+/', $a['cn'], $numA);
            preg_match('/\d+/', $b['cn'], $numB);
            return ($numA[0] ?? 0) - ($numB[0] ?? 0);
        });
        ?>
        <div id="<?= str_replace(" ", "", $department); ?>" class="departmentname"><?= htmlspecialchars($department); ?></div>
        <?php renderTable($users, 0); ?>
        <?php
    }
}} 
?>
                </tbody>
            </table>
            </td>
        </tr>
        </tbody>  
        </table>
    </div>
    <script>
        $(document).ready(function () {
            $("a[href^='#']").on("click", function (e) {
                e.preventDefault();

                var target = $($(this).attr("href"));

                $("html, body").animate({
                    scrollTop: target.offset().top - 200
                }, 1000, function() {
                    // Подсвечиваем секцию
                    target.addClass("highlight");

                    setTimeout(function() {
                        target.removeClass("highlight");
                    }, 1000); 
                });
            });
        });

        $('.upbtn').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 800);
        });

            const rows = document.querySelectorAll('tr');
            rows.forEach(row => { row.addEventListener('click', () => {
                row.classList.toggle('clicked');
            });
        });

        window.addEventListener("beforeprint", () => {
            if (window.matchMedia("(orientation: landscape)").matches) {
                alert("Пожалуйста, выберите книжную ориентацию при печати.");
            }
        });
    </script>
    <footer class="footer"></footer>
</body>
</html>