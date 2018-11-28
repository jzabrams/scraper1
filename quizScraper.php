<?php
$debug = false;
function getContents($str, $startDelimiter, $endDelimiter) {
  $contents = array();
  $startDelimiterLength = strlen($startDelimiter);
  $endDelimiterLength = strlen($endDelimiter);
  $startFrom = $contentStart = $contentEnd = 0;
  while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
    $contentStart += $startDelimiterLength;
    $contentEnd = strpos($str, $endDelimiter, $contentStart);
    if (false === $contentEnd) {
      break;
    }
    $question = substr($str, $contentStart, $contentEnd - $contentStart);
    $contents[] = ['question'=>$question];


    $index = count($contents) - 1;

    $contents[$index]['correctAnswer'] = 'A';

    $contents[$index]['possibleAnswers'] = ['A','B','C','D'];




    $startFrom = $contentEnd + $endDelimiterLength;
  }

  return $contents;
}





$startD = '<td colspan=2 valign="top" width="100%"><div class="vtbegenerated inlineVtbegenerated">';
$endD = '</div>';

$sample = file_get_contents("test.html");;
print_r( getContents($sample, $startD, $endD) );

$startD = '<span class="label">Selected Answer:</span>
        </td>
        <td valign="top">
          <div class=reviewQuestionsAnswerDiv>
            <span class="spacerImageHolder"></span><span class="answerNumLabelSpan">%</span>
            <span class=answerTextSpan>
              
<div class="vtbegenerated inlineVtbegenerated">';

$endD = '</div>
</span>
          </div>
        </td>
      </tr>
  <tr>
          <td valign="top">
      <span class="label">Answers:</span>
      </td>';



?>