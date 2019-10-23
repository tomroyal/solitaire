<?php

// solitaire cypher
// in php because why not?
// github.com/tomroyal / @tomroyal

// https://www.schneier.com/academic/solitaire/

$deck = ''; // init empty string for deck
$dops = 0; // counter for deck operations

// for convenience: convert a character to its numeric equivalent, starting with a = 01 through z = 26
function charToNumber($c) {
  // I'm going to be lazy and assume the input is valid..
  return (str_pad(strpos('0ABCDEFGHIJKLMNOPQRSTUVWXYZ', strtoupper($c)),2,'0',STR_PAD_LEFT));
}

// initilise a deck as a string (aces low, bridge order (CDHS), then two jokers)
// each card is numeric, 01 for ace clubs through 52 for KS
// call them joker A and joker B - JA and JB
function initBlankDeck($deck){
  for ($c = 1; $c <=52; $c++){
    $deck .= str_pad($c,2,'0',STR_PAD_LEFT).',';
  }
  $deck .= 'JA,JB,';
  return($deck);
}

// move nth card down x positions in deck

function showWorking($deck, $explanation){
  echo($explanation);
  echo("\r\n");
  if ($deck != ''){
    echo($deck);
    echo("\r\n");
  }
  echo("\r\n");
}

function moveCardByN($deck, $card, $steps){
  // move card $card down $deck by $steps positions

  $card = strval($card).',';

  // get current position of card in deck
  $currentpos = strpos($deck,$card);

  // remove card from deck
  $splitdeck = explode($card,$deck);
  $deck = $splitdeck[0].$splitdeck[1];

  // calc new position to insert at
  $insertpos = $currentpos + (3*$steps);
  if ($insertpos > strlen($deck)){
    $insertpos = $insertpos - strlen($deck);
  }

  // next, push card into deck at $currentpos + $steps cards
  $deck = substr_replace($deck,$card,$insertpos,0);

  // return
  return($deck);
}

function doSolitaire($deck,$encoding){
  // one iteration of the algorithm
  global $dops;

  // 1) find JA, move it one back or to position 2

  $deck = moveCardByN($deck, 'JA', 1);
  
  $dops++;
  showWorking($deck, 'Move JA back one');
  
  // 2) find JB, move it one back or to position 3

  $deck = moveCardByN($deck, 'JB', 2);
  
  $dops++;
  showWorking($deck, 'Move JB back two');

  // 3) "swap the cards above the first joker with the cards below the second joker" - ignoring JA JB

  // find position of jokers
  $firstj = strpos($deck,'J');
  $secondj = strpos($deck,'J',($firstj+1));

  // swap
  $deck = substr($deck,($secondj+3)).substr($deck,$firstj,($secondj+3-$firstj)).substr($deck,0,$firstj);
  
  $dops++;
  showWorking($deck, 'Swap cards above first J with those below second J');


  // 4) "Perform a count cut. Look at the bottom card. Convert it into a number from 1 through 53. (Use the bridge order of suits: clubs, diamonds, hearts, and spades. If the card is a club, it is the value shown.
  // If the card is a diamond, it is the value plus 13. If it is a heart, it is the value plus 26. If it is a spade, it is the value plus 39. Either joker is a 53.)
  // Count down from the top card that number. (I generally count 1 through 13 again and again if I have to; it's easier than counting to high numbers sequentially.)
  // Cut after the card that you counted down to, leaving the bottom card on the bottom."
  
  // get value of last card
  $lastcard = substr($deck, 159, 2);

  // is last card a joker?
  if (strpos($lastcard,'J') === false){
    // last card not a joker, so cut after it, leaving last card in place
    $lastcardvalue = intval($lastcard);
    $deck = substr($deck,(3*$lastcardvalue),(159-(3*$lastcardvalue))).substr($deck,0,(3*$lastcardvalue)).substr($deck,159);
    
    $dops++;
    showWorking($deck, 'Count cut on value of last card ('.$lastcardvalue.')');
  }
  else {
    // no need to cut deck as it does nothing..
    showWorking($deck, 'Last card is J, so no need to cut');
  }


  /*
  5. Find the output card. To do this, look at the top card. Convert it into a number from 1 through 53 in the same manner as step 4. Count down that many cards.
  (Count the top card as number one.) Write the card after the one you counted to on a piece of paper; don't remove it from the deck.
  (If you hit a joker, don't write anything down and start over again with step 1.)
  This is the first output card. Note that this step does not modify the state of the deck.
  */

  $topcard = substr($deck, 0, 2);
  if (strpos($topcard,'J') === false){
    // not joker
    $topcardvalue = intval($topcard);
  }
  else {
    $topcardvalue = 53;    
  }
  showWorking('', 'Top card value is '.$topcardvalue); 
  if ($encoding){
    // do a second cut count based on the character to encode    
    $deck = substr($deck,(3*$encoding),(159-(3*$encoding))).substr($deck,0,(3*$encoding)).substr($deck,159); 
    showWorking($deck, 'Encoding deck, so second count cut based on input ('.$encoding.')');  
    $outputvalue = '';
    $dops++;
  } 
  else {
    // work out the position in deck to check
    $outputposition = 3*$topcardvalue;
    $outputvalue = substr($deck, $outputposition, 2);
    // if joker, fail and try again. Otherwise, return that value
    if (strpos($outputvalue,'J') === false){
      // ok
      showWorking('', 'Output value is '.$outputvalue);
    }
    else {
      $outputvalue = 'JJ'; // indicates failure
      showWorking('', 'Output is joker so need to retry');
    }
    
  }
  $solresult['deck'] = $deck;
  $solresult['value'] = $outputvalue;
  return($solresult);

}

function prepareDeckFromPassphrase($deck,$keypassphrase){
  showWorking('', 'preparing deck from passphrase');
  $counter = 0;
  while ($counter < strlen($keypassphrase)){
    showWorking('', 'Init an encoding sol using character ('.substr($keypassphrase,$counter,1).')');  
    $sol = doSolitaire($deck,chartonumber(substr($keypassphrase,$counter,1)));
    $deck = $sol['deck'];
    $counter++;
  }
  showWorking($deck, 'Deck now initialised from '.$keypassphrase); 
  return($deck);
}

function generateCypherStream($deck,$length){
  showWorking('', 'preparing cypherstream');
  $counter = 0;
  $cypherstream = '';
  while ($counter < $length){
    showWorking('', 'Init a keystream sol, counter '.$counter);
    $sol = doSolitaire($deck,FALSE);
    $deck = $sol['deck'];
    if ($sol['value'] != 'JJ'){
      // valid result
      $cypherstream .= $sol['value'].',';
      $counter++;
      showWorking('', 'returned '.$sol['value'].' cs now '.$cypherstream);
    }
    else {
      showWorking('', 'returned joker so ignoring');
    }
  
  };
  showWorking('', 'cypherstream complete as '.$cypherstream);
  return($cypherstream);
  
}

function encryptThis($input,$cypherstream,$decrypt) {
  $counter = 0;
  $output = '';
  while ($counter < strlen($input)){
    if (!$decrypt){
      // decrypt false, so encrypt - addition
      $res = (charToNumber(substr($input,$counter,1)) + substr($cypherstream,($counter*3),2))%26;
    }
    else {
      // decrypt
      $inchar = charToNumber(substr($input,$counter,1));
      $cychar = substr($cypherstream,($counter*3),2);
      while ($inchar < $cychar){
        $inchar = $inchar + 26;
      }
      $res = ($inchar - $cychar)%26;
    }
    $output .= substr('0ABCDEFGHIJKLMNOPQRSTUVWXYZ',$res,1);
    $counter++;
  }  
  return ($output);
}

// OK, play some cards..

$keypassphrase = 'CRYPTONOMICON';
$input = 'SOLITAIRE';

// init the deck in standard order 
$deck = initBlankDeck($deck);
showWorking($deck, 'init blank deck');

// prepare the deck from the passphrase
$deck = prepareDeckFromPassphrase($deck,$keypassphrase);

// generate cypherstream
$cypherstream = generateCypherStream($deck,strlen($input));

// encrypt
$output = encryptThis($input,$cypherstream,FALSE); 
showWorking('', 'Complete. Plaintext '.$input.', keyphrase '.$keypassphrase.', ciphertext '.$output.' deck operations '.$dops);

// decrypt
// $output2 = encryptThis($output,$cypherstream,TRUE);

?>
