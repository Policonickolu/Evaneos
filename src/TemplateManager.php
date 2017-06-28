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
        
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        // REPLACE QUOTE PLACEHOLDERS

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        {

            $quoteFromRepo  = QuoteRepository::getInstance()->getById($quote->id);
            $site           = SiteRepository::getInstance()->getById($quote->siteId);
            $destination    = DestinationRepository::getInstance()->getById($quote->destinationId);

            // ----- ADD NEW PLACEHOLDERS TO BE REPLACED HERE -----

            $replacement = array();

            if(strpos($text, '[quote:destination_name]'))
                $replacement['[quote:destination_name]'] = $destination->countryName;

            if(strpos($text, '[quote:destination_link]'))
                $replacement['[quote:destination_link]'] = $site->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepo->id;

            if(strpos($text, '[quote:summary_html]'))
                $replacement['[quote:summary_html]'] = Quote::renderHtml($quoteFromRepo);

            if(strpos($text, '[quote:summary]'))
                $replacement['[quote:summary]'] = Quote::renderText($quoteFromRepo);


            // -----------------------------------------------------

            $text = str_replace(array_keys($replacement), $replacement, $text);

        }

        // REPLACE USER PLACEHOLDERS

        $user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();

        if($user) {

            // ----- ADD NEW PLACEHOLDERS TO BE REPLACED HERE -----

            $replacement = array();

            if(strpos($text, '[user:first_name]'))
                $replacement['[user:first_name]'] = ucfirst(mb_strtolower($user->firstname));

         
            // -----------------------------------------------------

            $text = str_replace(array_keys($replacement), $replacement, $text);

        }

        return $text;

    }

}
