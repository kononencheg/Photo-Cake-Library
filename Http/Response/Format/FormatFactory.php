<?php

namespace PhotoCake\Http\Response\Format;

class FormatFactory
{
    /**
     * @param string $name
     * @return \PhotoCake\Http\Response\Format\FormatInterface
     */
    public function create($name = null, \PhotoCake\Http\Request $request)
    {
        switch ($name) {
            case 'raw':
                return new RawFormat();

            case 'frame-callback':
                return new FrameCallbackFormat($request->fetch('__callback'));

            case 'json':
            default:
                return new JSONFormat();
        }
    }
}
