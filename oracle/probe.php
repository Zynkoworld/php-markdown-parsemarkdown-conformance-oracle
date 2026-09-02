<?php

declare(strict_types=1);

function parseMarkdown($markdown)
{
    $lines = explode("\n", $markdown);

    $isInList = false;

    foreach ($lines as &$line) {
        if (preg_match('/^####### (.*)/', $line, $matches)) {
            $line = "<p>" . trim($matches[0]) . "</p>";
        } elseif (preg_match('/^###### (.*)/', $line, $matches)) {
            $line = "<h6>" . trim($matches[1]) . "</h6>";
        } elseif (preg_match('/^##### (.*)/', $line, $matches)) {
            $line = "<h5>" . trim($matches[1]) . "</h5>";
        } elseif (preg_match('/^#### (.*)/', $line, $matches)) {
            $line = "<h4>" . trim($matches[1]) . "</h4>";
        } elseif (preg_match('/^### (.*)/', $line, $matches)) {
            $line = "<h3>" . trim($matches[1]) . "</h3>";
        } elseif (preg_match('/^## (.*)/', $line, $matches)) {
            $line = "<h2>" . trim($matches[1]) . "</h2>";
        } elseif (preg_match('/^# (.*)/', $line, $matches)) {
            $line = "<h1>" . trim($matches[1]) . "</h1>";
        }

        if (preg_match('/^\s*\*(.*)/', $line, $matches)) {
            if (!$isInList) {
                $isInList = true;
                $isBold = false;
                $isItalic = false;
                if (preg_match('/(.*)__(.*)__(.*)/', $matches[1], $matches2)) {
                    $matches[1] = $matches2[1] . '<strong>' . $matches2[2] . '</strong>' . $matches2[3];
                    $isBold = true;
                }

                if (preg_match('/(.*)_(.*)_(.*)/', $matches[1], $matches3)) {
                    $matches[1] = $matches3[1] . '<em>' . $matches3[2] . '</em>' . $matches3[3];
                    $isItalic = true;
                }

                if ($isItalic || $isBold) {
                    $line = "<ul><li>" . trim($matches[1]) . "</li>";
                } else {
                    $line = "<ul>";
                    $line .= "<li>";
                    $line .= trim($matches[1]);
                    $line .= "</li>";
                }
            } else {
                $isBold = false;
                $isItalic = false;
                if (preg_match('/(.*)__(.*)__(.*)/', $matches[1], $matches2)) {
                    $matches[1] = $matches2[1] . '<strong>' . $matches2[2] . '</strong>' . $matches2[3];
                    $isBold = true;
                }

                if (preg_match('/(.*)_(.*)_(.*)/', $matches[1], $matches3)) {
                    $matches[1] = $matches3[1] . '<em>' . $matches3[2] . '</em>' . $matches3[3];
                    $isItalic = true;
                }

                if ($isItalic || $isBold) {
                    $line = "<li>" . trim($matches[1]) . "</li>";
                } else {
                    $line = "<li>";
                    $line .= trim($matches[1]);
                    $line .= "</li>";
                }
            }
        } else {
            if ($isInList) {
                $line = "</ul>" . $line;
                $isInList = false;
            }
        }

        if (!preg_match('/<h|<ul|<p|<li/', $line)) {
            if (preg_match('/^<\/ul>(.*)/', $line, $matches)) {
                $line = "</ul><p>" . trim($matches[1]) . "</p>";
            } else {
                $line = "<p>$line</p>";
            }
        }

        if (preg_match('/(.*)__(.*)__(.*)/', $line, $matches)) {
            $line = $matches[1] . '<strong>' . $matches[2] . '</strong>' . $matches[3];
        }

        if (preg_match('/(.*)_(.*)_(.*)/', $line, $matches)) {
            $line = $matches[1] . '<em>' . $matches[2] . '</em>' . $matches[3];
        }
    }
    $html = join($lines);
    if ($isInList) {
        $html .= '</ul>';
    }
    return $html;
}

$__in = json_decode('["This will be a paragraph", "_This will be italic_", "__This will be bold__", "This will _be_ __mixed__", "# This will be an h1", "## This will be an h2", "### This will be an h3", "#### This will be an h4", "##### This will be an h5", "###### This will be an h6", "####### This will not be an h7", "* Item 1\n* Item 2", "# Header!\n* __Bold Item__\n* _Italic Item_", "# This is a header with # and * in the text", "* Item 1 with a # in the text\n* Item 2 with * in the text", "This is a paragraph with # and * in the text"]', true);
$__out = [];
foreach ($__in as $x) {
  try { $__out[] = ["ok" => true, "v" => parseMarkdown($x)]; }
  catch (\Throwable $e) { $__out[] = ["ok" => false, "e" => get_class($e)]; }
}
echo json_encode(["out" => $__out]);
