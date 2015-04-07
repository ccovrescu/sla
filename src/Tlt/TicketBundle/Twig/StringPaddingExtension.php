<?php
namespace Tlt\TicketBundle\Twig;

use Twig_Extension;

class StringPaddingExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('str_pad', array($this, 'stringPadding')),
        );
    }

    public function stringPadding($input, $pad_length, $pad_string=' ', $pad_type=STR_PAD_BOTH)
    {
        return str_pad($input, $pad_length, $pad_string, $pad_type);
    }

    public function getName()
    {
        return 'str_pad_extension';
    }
}