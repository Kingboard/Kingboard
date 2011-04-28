<?php
/*
 MIT License
 Copyright (c) 2011 Peter Petermann

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.

*/
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
