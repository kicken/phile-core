<!DOCTYPE html>
<html>
<head>
    <title>PhileCMS Development Error Handler</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
        body {
            font: normal normal normal 14px/1.4 Arial, sans-serif;
            background: white;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            -moz-osx-font-smoothing: grayscale;
        }

        header {
            position: absolute;
        }

        pre {
            font-family: "Lucida Console", Monaco, monospace;
            margin: 0;
            color: rgba(255, 255, 255, 0.5);
            background: #1f1f1f;
        }

        pre.entry {
            color: #FFF;
            padding: 4px 2px 2px 2px;
            margin-bottom: 10px;
            background: #1f1f1f;
        }

        pre .row {
            padding-left: 30px;
        }

        pre .currentRow {
            background-color: #A33236;
            color: white;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.2);
            display: block;
        }

        pre .currentRow .index {
            color: white;
            font-weight: normal;
        }

        .main {
            margin: 30px 30px 30px 140px;
            padding: 0;
            padding-bottom: 2px;
        }

        .header {
            font-size: 14px;
            background-color: #a33236;
            color: #fff;
            padding: 8px;
            margin: 0 0 6px 0;
        }

        .body {
            padding: 0 8px;
            margin: 0 0 6px 0;
        }

        .red {
            color: #a33236;
        }

        .exception {
            color: #acc23a;
            font-weight: bold;
            font-family: 'Courier New', serif;
        }

        .file {
            color: #acc23a;
            font-weight: bold;
            font-family: 'Courier New', serif;
        }

        .hint {
            color: #101010;
        }

        .divider {
            color: #fff;
        }

        .separator {
            color: #fff;
        }

        .class {
            color: #acc23a;
            font-style: italic;
        }

        .number, .others {
            font-weight: bold;
            color: #6894d1;
        }

        .index {
            color: #b581cf;
            font-weight: bold;
            margin-left: 5px;
        }

        .funcArguments {
            color: #fff;
        }

        .filename {
            color: white;
            font-size: 10px;
            margin-left: 5px;
            padding: 3px;
        }

        a {
            color: inherit;
        }
    </style>
</head>
<body>
<header>
    <img
        src="data:image/png;base64,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"
        title="PhileCMS" alt="Phile Logo">
</header>
<div class="main">
    <div class="header">PhileCMS Development Error Handler</div>
    <div class="body">
        <h2><?=$type?></h2>
        <p>
            <strong class="red">
                <?=$exception_message?> [<?=$exception_code?>]
            </strong>
            <?=$wiki_link?>
            <br/>
            <span class="exception"><?=$exception_class?></span>
            triggered in file
            <span class="file">
                <?=$exception_file?></span> on line <span class="line"><?=$exception_line?>
            </span>.
        </p>
        <?=$exception_fragment?>
        <?php if (isset($exception_backtrace)) : ?>
            <h2>Backtrace</h2>
            <?=$exception_backtrace?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
