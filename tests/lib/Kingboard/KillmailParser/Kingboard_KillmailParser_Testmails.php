<?php
/**
 * DataProvider for testmails
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailParser_Testmails
{


   public function getTestmails() {
       $dir = __DIR__ . '/data/mails/*.txt';
       $mails = glob($dir);
       $data = array();
       foreach ($mails as $mailFile) {
            $resultFile = substr($mailFile, 0 , -3) . 'php';
            if (is_file($resultFile)) {
                $result = require $resultFile;
                $result['plainMail'] = file_get_contents($mailFile);
                $data[] = array(
                    $result['plainMail'],
                    $result
                );
            }
       }
       return $data;
   }

   /**
    * Not strict equal
    * Values can have different offsets, as long as they have the same key
    *
    * @param array $expected
    * @param array $actual
    * @return boolean
    */
   public function compareArrays($expected, $actual, $parentIndex = '') {
       foreach ($expected as $key => $value) {
           if (!isset($actual[$key])) {
               throw new UnexpectedValueException("Invalid in $parentIndex $key, empty");
           }
           if (is_array($value)) {
               if (!is_array($actual[$key])) {
                   throw new UnexpectedValueException("Invalid in $parentIndex $key, items missing");
               }
               $result = $this->compareArrays($value, $actual[$key], $parentIndex . '.' . $key);
           }
           else {
               if ((string) $value !== (string) $actual[$key]) {
                   throw new UnexpectedValueException("Invalid in $parentIndex . $key, \nShould: '$value'\nIs: '{$actual[$key]}'\n");
               }
           }
       }
       return true;
   }
}
