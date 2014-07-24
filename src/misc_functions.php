<?php
/*
 * This file is part of StratumAPI <https://github.com/Contex/StratumAPI>.
 *
 * StratumAPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * StratumAPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class MiscFunctions
{
	public static function JSONPrettyPrint($json)
	{
	    $result = '';
	    $level = 0;
	    $in_quotes = false;
	    $in_escape = false;
	    $ends_line_level = NULL;
	    $json_length = strlen( $json );

	    for ($i = 0; $i < $json_length; $i++) {
	        $char = $json[$i];
	        $new_line_level = NULL;
	        $post = "";
	        if ($ends_line_level !== NULL) {
	            $new_line_level = $ends_line_level;
	            $ends_line_level = NULL;
	        }
	        if ($in_escape) {
	            $in_escape = false;
	        } else if ($char === '"') {
	            $in_quotes = !$in_quotes;
	        } else if (!$in_quotes) {
	            switch ($char) {
	                case '}': case ']':
	                    $level--;
	                    $ends_line_level = NULL;
	                    $new_line_level = $level;
	                    break;
	                case '{': case '[':
	                    $level++;
	                case ',':
	                    $ends_line_level = $level;
	                    break;
	                case ':':
	                    $post = " ";
	                    break;
	                case " ": case "\t": case "\n": case "\r":
	                    $char = "";
	                    $ends_line_level = $new_line_level;
	                    $new_line_level = NULL;
	                    break;
	            }
	        } else if ($char === '\\') {
	            $in_escape = true;
	        }
	        if ($new_line_level !== NULL) {
	            $result .= "\n" . str_repeat("\t", $new_line_level);
	        }
	        $result .= $char . $post;
	    }
	    return $result;
	}
}
?>