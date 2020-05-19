<?php
/**
 * @package dompdf
 * @link    https://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license https://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Dompdf\Positioner;

use Dompdf\FrameDecorator\AbstractFrameDecorator;
use Dompdf\FrameDecorator\Inline as InlineFrameDecorator;
use Dompdf\FrameDecorator\Block as BlockFrameDecorator;
use Dompdf\Exception;

/**
 * Positions inline frames
 *
 * @package dompdf
 */
class Inline extends AbstractPositioner
{

    function __construct(AbstractFrameDecorator $frame)
    {
        parent::__construct($frame);
    }

    //........................................................................

    function position()
    {
        /**
         * Find our nearest block level parent and access its lines property.
         * @var BlockFrameDecorator
         */
        $p = $this->_frame->find_block_parent();

        // Debugging code:

//     Helpers::pre_r("\nPositioning:");
//     Helpers::pre_r("Me: " . $this->_frame->get_node()->nodeName . " (" . spl_object_hash($this->_frame->get_node()) . ")");
//     Helpers::pre_r("Parent: " . $p->get_node()->nodeName . " (" . spl_object_hash($p->get_node()) . ")");

        // End debugging

        if (!$p)
            throw new Exception("No block-level parent found.  Not good.");

        $f = $this->_frame;

        $cb = $f->get_containing_block();
        $line = $p->get_current_line_box();

        // Skip the page break if in a fixed position element
        $is_fixed = false;
        while ($f = $f->get_parent()) {
            if ($f->get_style()->position === "fixed") {
                $is_fixed = true;
                break;
            }
        }

        $f = $this->_frame;

        if (!$is_fixed && $f->get_parent() &&
            $f->get_parent() instanceof InlineFrameDecorator &&
            $f->is_text_node()
        ) {

            $min_max = $f->get_reflower()->get_min_max_width();

            // If the frame doesn't fit in the current line, a line break occurs
            if ($min_max["min"] > ($cb["w"] - $line->left - $line->w - $line->right)) {
                $p->add_line();
            }
        }

        $f->set_position($cb["x"] + $line->w, $line->y);

    }
}
