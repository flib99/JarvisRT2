<?php

/*
 * Copyright (C) 2015 joshwalls
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of output
 *
 * @author joshwalls
 */

require_once '../inc/const.php';

class output
{
    function getTTSURL()
    {
        $random = rand(1, 2);
        
        if ($random == 1)
        {
            $url[0] = "https://tts.neospeech.com/rest_1_1.php?method=ConvertSimple&email=me@joshwalls.co.uk&accountId=0e2a67153a&loginKey=LoginKey&loginPassword=96084cbdff0489466994&voice=TTS_NEOBRIDGET_DB&outputFormat=FORMAT_WAV&sampleRate=16&text=";
            $url[1] = "https://tts.neospeech.com/rest_1_1.php?method=GetConversionStatus&email=me@joshwalls.co.uk&accountId=0e2a67153a&conversionNumber=";
        }
        else
        {
            $url[0] = "https://tts.neospeech.com/rest_1_1.php?method=ConvertSimple&email=josh@wallsfamily.co.uk&accountId=004a11dcb6&loginKey=LoginKey&loginPassword=a624702570f18209e0d7&voice=TTS_NEOBRIDGET_DB&outputFormat=FORMAT_WAV&sampleRate=16&text=";
            $url[1] = "https://tts.neospeech.com/rest_1_1.php?method=GetConversionStatus&email=josh@wallsfamily.co.uk&accountId=004a11dcb6&conversionNumber=";
        }
        
        return $url;
    }
    
    function checkMessageLength($message)
    {
        if (str_word_count($message) > 100)
        {
            $lineSplit = preg_replace( '~((?:\S*?\s){100})~', "$1\n", $message );

            $chunk = explode("\n", $lineSplit);
        }
        else
        {
            $chunk[0] = $message;
        }
        
        return $chunk;
    }
    
    function getAudio($chunk)
    {
        foreach ($chunk as $sentance)
        {
            $conversionNumber = $this->getConvertSimple($sentance);
            
            $this->getConversionStatus($conversionNumber);
        }
    }
    
    function getConvertSimple($sentance)
    {
        $response = simplexml_load_string(file_get_contents($url[0] . urlencode($sentance)));
        
        if ($response['resultCode'] != '0')
        {
            $this->offline($sentance);
            return;
        }
        
        return $response['conversionNumber'];
    }
    
    function getConversionStatus($conversionNumber)
    {
        $audio = simplexml_load_string(file_get_contents($url[1] . urlencode($conversionNumber)));
        
        $startTime = time();
        
        while ($audio['downloadUrl'] == "")
        {
            if((time() - $startTime) > 30)
            {
                exec("aplay " . escapeshellarg(HOME . "/inc/error.wav"));
                return FALSE;
            }
            
            $audio = simplexml_load_string(file_get_contents($url[1] . urlencode($conversionNumber)));
        }
        
        return $audio;
    }
    
    function getDownloadURL($audio)
    {
        return $audio['downloadUrl'];
    }
    
    function playAudio($downloadURL)
    {
        exec("wget -O /tmp/tts.wav " . escapeshellarg($downloadURL));
        exec("aplay /tmp/tts.wav");
        exec("rm /tmp/tts.wav");
    }
    
    function say($message)
    {
        $url = $this->getTTSURL();
        
        $chunks = $this->checkMessageLength($message);
        
        
        
        
    }
    
    function offline($sentance)
    {
        exec ("/usr/local/bin/simple_google_tts -p en " . escapeshellarg($sentance));
    }
}
