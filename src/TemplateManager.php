<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $textReplaced = clone($tpl);
        $textReplaced->subject = $this->computeText($textReplaced->subject, $data);
        $textReplaced->content = $this->computeText($textReplaced->content, $data);

        return $textReplaced;
    }


    private function computeText($text, array $data)
    {
        
        $replacements = array();

        $replacements = $this->prepareQuoteReplacement($text, $data, $replacements);
        $replacements = $this->prepareUserReplacement($text, $data, $replacements);

        if(!empty($replacements))
            $text = str_replace(array_keys($replacements), $replacements, $text);

        return $text;

    }


    private function prepareQuoteReplacement($text, array $data, array $replacements)
    {

       $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        {

            $quoteFromRepo  = QuoteRepository::getInstance()->getById($quote->id);
            $site           = SiteRepository::getInstance()->getById($quote->siteId);
            $destination    = DestinationRepository::getInstance()->getById($quote->destinationId);

            // ----- QUOTE PLACEHOLDERS TO BE REPLACED -----

            if(strpos($text, '[quote:destination_name]'))
                $replacements['[quote:destination_name]'] = $destination->countryName;

            if(strpos($text, '[quote:destination_link]'))
                $replacements['[quote:destination_link]'] = $site->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepo->id;

            if(strpos($text, '[quote:summary_html]'))
                $replacements['[quote:summary_html]'] = Quote::renderHtml($quoteFromRepo);

            if(strpos($text, '[quote:summary]'))
                $replacements['[quote:summary]'] = Quote::renderText($quoteFromRepo);




            // -----
            
        }

        return $replacements;
    }


    private function prepareUserReplacement($text, array $data, array $replacements)
    {

        $appContext = ApplicationContext::getInstance();

        $user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $appContext->getCurrentUser();

        // ----- USER PLACEHOLDERS TO BE REPLACED -----

        if(strpos($text, '[user:first_name]'))
            $replacements['[user:first_name]'] = ucfirst(mb_strtolower($user->firstname));


        // -----

        return $replacements;
    }
}
