<?php

namespace Aoyagi\AoyagiDiary\service;

interface DiaryDownloadInterface
{
    public function downloadList(array $diaries): void;
}
